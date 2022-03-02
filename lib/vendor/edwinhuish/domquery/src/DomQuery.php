<?php

namespace DQ;

/**
 * Class DomQuery
 * @package DQ
 *
 * @property-read string $text
 * @property-read string $plaintext
 * @property-read string $html
 * @property-read string $innerHTML
 * @property-read string $outerHTML
 *
 */
class DomQuery extends DomQueryNodes
{
    /**
     * Node data
     *
     * @var array
     */
    private static $node_data = array();

    /**
     * Get the combined text contents of each element in the set of matched elements, including their descendants,
     * or set the text contents of the matched elements.
     *
     * @param  string|null  $val
     *
     * @return $this|string|null
     */
    public function text($val = null)
    {
        if ($val !== null) { // set node value for all nodes
            foreach ($this->nodes as $node) {
                (array)$val === $val ? $val = json_encode($val) : null;
            }

            return $this;
        }
        if ($node = $this->getFirstElmNode()) { // get value for first node
            return $node->nodeValue;
        }

        return null;
    }

    /**
     * Get the HTML contents of the first element in the set of matched elements
     *
     * @param  string|null  $html_string
     *
     * @return $this|string
     */
    public function html($html_string = null)
    {
        if ($html_string !== null) { // set html for all nodes
            foreach ($this as $node) {
                /* @var DomQuery $node */
                $node->get(0)->nodeValue = '';
                $node->append($html_string);
            }

            return $this;
        }
        // get html for first node
        return $this->getInnerHtml();
    }

    /**
     * Get the value of an attribute for the first element in the set of matched elements
     * or set one or more attributes for every matched element.
     *
     * @param  string  $name
     * @param  string  $val
     *
     * @return $this|string|string[]|null
     */
    public function attr(string $name, $val = null)
    {
        if ($val !== null) { // set attribute for all nodes
            foreach ($this->getElements() as $node) {
                try {
                    $node->setAttribute($name, $val);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            return $this;
        }
        if ($node = $this->getFirstElmNode()) { // get attribute value for first element

            if ('*' === $name) {
                $attrs = [];
                /* @var \DOMNode $node */
                foreach ($node->attributes as $attr) {
                    $attrs[] = [$attr->nodeName => $attr->nodeValue];
                }
                return $attrs;
            }

            return $node->getAttribute($name);
        }

        return null;
    }

    /**
     * Store arbitrary data associated with the matched elements or return the value at
     * the named data store for the first element in the set of matched elements.
     *
     * @param  string  $key
     * @param $val
     *
     * @return $this|string|object
     */
    public function data(string $key = null, $val = null)
    {
        $doc_hash = spl_object_hash($this->document);

        if ($val !== null) { // set data for all nodes
            if ( ! isset(self::$node_data[$doc_hash])) {
                self::$node_data[$doc_hash] = array();
            }
            foreach ($this->getElements() as $node) {
                if ( ! isset(self::$node_data[$doc_hash][self::getElementId($node)])) {
                    self::$node_data[$doc_hash][self::getElementId($node)] = (object) array();
                }
                self::$node_data[$doc_hash][self::getElementId($node)]->$key = $val;
            }
            return $this;
        }

        if ($node = $this->getFirstElmNode()) { // get data for first element
            if (isset(self::$node_data[$doc_hash]) && isset(self::$node_data[$doc_hash][self::getElementId($node)])) {
                if ($key === null) {
                    return self::$node_data[$doc_hash][self::getElementId($node)];
                } elseif (isset(self::$node_data[$doc_hash][self::getElementId($node)]->$key)) {
                    return self::$node_data[$doc_hash][self::getElementId($node)]->$key;
                }
            }
            if ($key === null) { // object with all data
                $data = array();
                foreach ($node->attributes as $attr) {
                    if (strpos($attr->nodeName, 'data-') === 0) {
                        $val = $attr->nodeValue[0] === '{' ? json_decode($attr->nodeValue) : $attr->nodeValue;
                        $data[substr($attr->nodeName, 5)] = $val;
                    }
                }
                return (object) $data;
            }
            if ($data = $node->getAttribute('data-'.$key)) {
                $val = $data[0] === '{' ? json_decode($data) : $data;
                return $val;
            }
        }

        return null;
    }

    /**
     * Remove a previously-stored piece of data.
     *
     * @param  string|string[]  $name
     *
     * @return void
     */
    public function removeData($name = null)
    {
        $remove_names = \is_array($name) ? $name : explode(' ', $name);
        $doc_hash = spl_object_hash($this->document);

        if ( ! isset(self::$node_data[$doc_hash])) {
            return;
        }

        foreach ($this->getElements() as $node) {
            if ( ! $node->hasAttribute('dqn_tmp_id')) {
                continue;
            }

            $node_id = self::getElementId($node);

            if (isset(self::$node_data[$doc_hash][$node_id])) {
                if ($name === null) {
                    self::$node_data[$doc_hash][$node_id] = null;
                } else {
                    foreach ($remove_names as $remove_name) {
                        if (isset(self::$node_data[$doc_hash][$node_id]->$remove_name)) {
                            self::$node_data[$doc_hash][$node_id]->$remove_name = null;
                        }
                    }
                }
            }
        }
    }

    /**
     * Convert css string to array
     *
     * @param  string containing style properties
     *
     * @return array with name-value as style properties
     */
    private static function parseStyle(string $css)
    {
        $statements = explode(';', preg_replace('/\s+/s', ' ', $css));
        $styles = array();

        foreach ($statements as $statement) {
            if ($p = strpos($statement, ':')) {
                $key = trim(substr($statement, 0, $p));
                $value = trim(substr($statement, $p + 1));
                $styles[$key] = $value;
            }
        }

        return $styles;
    }

    /**
     * Convert css name-value array to string
     *
     * @param  array with style properties
     *
     * @return string containing style properties
     */
    private static function getStyle(array $array)
    {
        $styles = '';
        foreach ($array as $key => $value) {
            $styles .= $key.': '.$value.';';
        }
        return $styles;
    }

    /**
     * Get the value of a computed style property for the first element in the set of matched elements
     * or set one or more CSS properties for every matched element.
     *
     * @param  string  $name
     * @param  string  $val
     *
     * @return $this|string
     */
    public function css(string $name, $val = null)
    {
        if ($val !== null) { // set css for all nodes
            foreach ($this->getElements() as $node) {
                $style = self::parseStyle($node->getAttribute('style'));
                $style[$name] = $val;
                $node->setAttribute('style', self::getStyle($style));
            }
            return $this;
        }
        if ($node = $this->getFirstElmNode()) { // get css value for first element
            $style = self::parseStyle($node->getAttribute('style'));
            if (isset($style[$name])) {
                return $style[$name];
            }
        }

        return null;
    }

    /**
     * Remove an attribute from each element in the set of matched elements.
     * Name can be a space-separated list of attributes.
     *
     * @param  string|string[]  $name
     *
     * @return $this
     */
    public function removeAttr($name)
    {
        $remove_names = \is_array($name) ? $name : explode(' ', $name);

        foreach ($this->getElements() as $node) {
            foreach ($remove_names as $remove_name) {
                $node->removeAttribute($remove_name);
            }
        }

        return $this;
    }

    /**
     * Adds the specified class(es) to each element in the set of matched elements.
     *
     * @param  string|string[]  $class_name  class name(s)
     *
     * @return $this
     */
    public function addClass($class_name)
    {
        $add_names = \is_array($class_name) ? $class_name : explode(' ', $class_name);

        foreach ($this->getElements() as $node) {
            $node_classes = array();
            if ($node_class_attr = $node->getAttribute('class')) {
                $node_classes = explode(' ', $node_class_attr);
            }
            foreach ($add_names as $add_name) {
                if ( ! \in_array($add_name, $node_classes, true)) {
                    $node_classes[] = $add_name;
                }
            }
            if (\count($node_classes) > 0) {
                $node->setAttribute('class', implode(' ', $node_classes));
            }
        }

        return $this;
    }

    /**
     * Determine whether any of the matched elements are assigned the given class.
     *
     * @param  string  $class_name
     *
     * @return boolean
     */
    public function hasClass($class_name)
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof \DOMElement && $node_class_attr = $node->getAttribute('class')) {
                $node_classes = explode(' ', $node_class_attr);
                if (\in_array($class_name, $node_classes, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Remove a single class, multiple classes, or all classes from each element in the set of matched elements.
     *
     * @param  string|string[]  $class_name
     *
     * @return $this
     */
    public function removeClass($class_name = '')
    {
        $remove_names = \is_array($class_name) ? $class_name : explode(' ', $class_name);

        foreach ($this->nodes as $node) {
            if ($node instanceof \DOMElement && $node->hasAttribute('class')) {
                $node_classes = preg_split('#\s+#s', $node->getAttribute('class'));
                $class_removed = false;

                if ($class_name === '') { // remove all
                    $node_classes = array();
                    $class_removed = true;
                } else {
                    foreach ($remove_names as $remove_name) {
                        $key = array_search($remove_name, $node_classes, true);
                        if ($key !== false) {
                            unset($node_classes[$key]);
                            $class_removed = true;
                        }
                    }
                }
                if ($class_removed) {
                    $node->setAttribute('class', implode(' ', $node_classes));
                }
            }
        }

        return $this;
    }

    /**
     * Remove a single class, multiple classes, or all classes from each element in the set of matched elements.
     *
     * @param  string|string[]  $class_name
     *
     * @return $this
     */
    public function toggleClass($class_name = '')
    {
        $toggle_names = \is_array($class_name) ? $class_name : explode(' ', $class_name);

        foreach ($this as $node) {
            foreach ($toggle_names as $toggle_class) {
                if ( ! $node->hasClass($toggle_class)) {
                    $node->addClass($toggle_class);
                } else {
                    $node->removeClass($toggle_class);
                }
            }
        }

        return $this;
    }

    /**
     * Get the value of a property for the first element in the set of matched elements
     * or set one or more properties for every matched element.
     *
     * @param  string  $name
     * @param  string  $val
     *
     * @return $this|mixed|null
     */
    public function prop(string $name, $val = null)
    {
        if ($val !== null) { // set attribute for all nodes
            foreach ($this->nodes as $node) {
                $node->$name = $val;
            }

            return $this;
        }
        // get property value for first element
        if ($name === 'outerHTML') {
            return $this->getOuterHtml();
        }
        if ($node = $this->getFirstElmNode()) {
            if (isset($node->$name)) {
                return $node->$name;
            }
        }
        return null;
    }

    /**
     * Get the children of each element in the set of matched elements, including text and comment nodes.
     *
     * @return self
     */
    public function contents()
    {
        return $this->children(false);
    }

    /* @noinspection PhpDocMissingThrowsInspection */
    /**
     * Get the children of each element in the set of matched elements, optionally filtered by a selector.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|false|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function children($selector = null)
    {
        $result = $this->createChildInstance();

        if ( ! isset($this->document) || $this->length <= 0) {
            return $result;
        }

        if (isset($this->root_instance) || $this->getXpathQuery()) {
            foreach ($this->nodes as $node) {
                if ($node->hasChildNodes()) {
                    /* @noinspection PhpUnhandledExceptionInspection */
                    $result->loadDomNodeList($node->childNodes);
                }
            }
        } else {
            /* @noinspection PhpUnhandledExceptionInspection */
            $result->loadDomNodeList($this->document->childNodes);
        }

        if ($selector !== false) { // filter out text nodes
            $filtered_elements = array();
            foreach ($result->getElements() as $result_elm) {
                $filtered_elements[] = $result_elm;
            }
            $result->nodes = $filtered_elements;
            $result->length = \count($result->nodes);
        }

        if ($selector) {
            $result = $result->filter($selector);
        }

        return $result;
    }

    /**
     * Get the siblings of each element in the set of matched elements, optionally filtered by a selector.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function siblings($selector = null)
    {
        $result = $this->createChildInstance();

        if (isset($this->document) && $this->length > 0) {
            foreach ($this->nodes as $node) {
                if ($node->parentNode) {
                    foreach ($node->parentNode->childNodes as $sibling) {
                        if ($sibling instanceof \DOMElement && ! $sibling->isSameNode($node)) {
                            $result->addDomNode($sibling);
                        }
                    }
                }
            }

            if ($selector) {
                $result = $result->filter($selector);
            }
        }

        return $result;
    }

    /**
     * Get the parent of each element in the current set of matched elements, optionally filtered by a selector
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function parent($selector = null)
    {
        /* @var DomQuery $resule */
        $result = $this->createChildInstance();

        if (isset($this->document) && $this->length > 0) {
            foreach ($this->nodes as $node) {
                if ($node->parentNode) {
                    $result->addDomNode($node->parentNode);
                }
            }

            if ($selector) {
                $result = $result->filter($selector);
            }
        }

        return $result;
    }

    /**
     * For each element in the set, get the first element that matches the selector
     * by testing the element itself and traversing up through its ancestors in the DOM tree.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector  selector expression to match elements against
     *
     * @return self
     */
    public function closest($selector)
    {
        $result = $this->createChildInstance();

        if ( ! isset($this->document) || $this->length <= 0) {
            return $result;
        }

        foreach ($this->nodes as $node) {
            $current = $node;

            while ($current instanceof \DOMElement) {
                if (self::create($current)->is($selector)) {
                    $result->addDomNode($current);
                    break;
                }
                $current = $current->parentNode;
            }
        }


        return $result;
    }

    /**
     * Remove elements from the set of matched elements.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector
     *
     * @return self
     */
    public function not($selector)
    {
        $result = $this->createChildInstance();

        if ($this->length > 0) {
            if (\is_callable($selector)) {
                foreach ($this->nodes as $index => $node) {
                    if ( ! $selector($node, $index)) {
                        $result->addDomNode($node);
                    }
                }
            } else {
                $selection = self::create($this->document)->find($selector);

                if ($selection->length > 0) {
                    foreach ($this->nodes as $node) {
                        $matched = false;
                        foreach ($selection as $result_node) {
                            /* @var \DOMNode $result_node */
                            if ($result_node->isSameNode($node)) {
                                $matched = true;
                                break 1;
                            }
                        }
                        if ( ! $matched) {
                            $result->addDomNode($node);
                        }
                    }
                } else {
                    $result->addNodes($this->nodes);
                }
            }
        }

        return $result;
    }

    /**
     * Reduce the set of matched elements to those that match the selector
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector
     * @param  string|self|\DOMNodeList|\DOMNode|\DOMDocument  $context
     *
     * @return self
     */
    public function add($selector, $context = null)
    {
        $result = $this->createChildInstance();
        $result->nodes = $this->nodes;

        $selection = $this->getTargetResult($selector, $context);

        foreach ($selection as $selection_node) {
            if ( ! $result->is($selection_node)) {
                if ($result->document === $selection_node->document) {
                    $new_node = $selection_node->get(0);
                } else {
                    $new_node = $this->document->importNode($selection_node->get(0), true);
                }

                $result->addDomNode($new_node);
            }
        }

        return $result;
    }

    /**
     * Reduce the set of matched elements to those that match the selector
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector
     *
     * @return self
     */
    public function filter($selector)
    {
        /* @var DomQuery $result */
        $result = $this->createChildInstance();

        if ($this->length > 0) {
            if (\is_callable($selector)) {
                foreach ($this->nodes as $index => $node) {
                    if ($selector($node, $index)) {
                        $result->addDomNode($node);
                    }
                }
            } else {
                $selection = self::create($this->document)->find($selector);

                foreach ($selection as $result_node) {
                    /* @var \DOMNode $result_node */
                    foreach ($this->nodes as $node) {
                        if ($result_node->isSameNode($node)) {
                            $result->addDomNode($node);
                            break 1;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Create a deep copy of the set of matched elements (does not clone attached data).
     *
     * @return self
     */
    public function clone()
    {
        return $this->createChildInstance($this->getClonedNodes());
    }

    /**
     *  Get the position of the first element within the DOM, relative to its sibling elements.
     *  Or get the position of the first node in the result that matches the selector.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector
     *
     * @return int $position
     */
    public function index($selector = null)
    {
        if ($selector === null) {
            if ($node = $this->getFirstElmNode()) {
                $position = 0;
                while ($node && ($node = $node->previousSibling)) {
                    if ($node instanceof \DOMElement) {
                        $position++;
                    } else {
                        break;
                    }
                }
                return $position;
            }
        } else {
            foreach ($this as $key => $node) {
                if ($node->is($selector)) {
                    return $key;
                }
            }
        }

        return -1;
    }

    /**
     * Check if any node matches the selector
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector
     *
     * @return boolean
     */
    public function is($selector)
    {
        if ($this->length > 0) {
            if (\is_callable($selector)) {
                foreach ($this->nodes as $index => $node) {
                    if ($selector($node, $index)) {
                        return true;
                    }
                }
            } else {
                $selection = self::create($this->document)->find($selector);

                foreach ($selection->nodes as $result_node) {
                    foreach ($this->nodes as $node) {
                        if ($result_node->isSameNode($node)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Reduce the set of matched elements to those that have a descendant that matches the selector
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode  $selector
     *
     * @return self
     */
    public function has($selector)
    {
        /* @var DomQuery $result */
        $result = $this->createChildInstance();

        if ($this->length > 0) {
            foreach ($this as $node) {
                if ($node->find($selector)->length > 0) {
                    $result->addDomNode($node->get(0));
                }
            }
        }

        return $result;
    }

    /**
     * Reduce the set of matched elements to a subset specified by the offset and length (php like)
     *
     * @param  integer  $offset
     * @param  integer  $length
     *
     * @return self
     */
    public function slice($offset = 0, $length = null)
    {
        $result = $this->createChildInstance();
        $result->nodes = \array_slice($this->nodes, $offset, $length);
        $result->length = \count($result->nodes);
        return $result;
    }

    /**
     * Reduce the set of matched elements to the one at the specified index.
     *
     * @param  integer  $index
     *
     * @return self
     */
    public function eq($index)
    {
        return $this->slice($index, 1);
    }

    /**
     * Returns DomQuery with first node
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function first($selector = null)
    {
        $result = $this[0];
        if ($selector) {
            $result = $result->filter($selector);
        }
        return $result;
    }

    /**
     * Returns DomQuery with last node
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function last($selector = null)
    {
        $result = $this[$this->length - 1];
        if ($selector) {
            $result = $result->filter($selector);
        }
        return $result;
    }

    /**
     * Returns DomQuery with immediately following sibling of all nodes
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function next($selector = null)
    {
        $result = $this->createChildInstance();

        if (isset($this->document) && $this->length > 0) {
            foreach ($this->nodes as $node) {
                if ($next = self::getNextElement($node)) {
                    $result->addDomNode($next);
                }
            }

            if ($selector) {
                $result = $result->filter($selector);
            }
        }

        return $result;
    }

    /**
     * Get all following siblings of each element in the set of matched elements, optionally filtered by a selector.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function nextAll($selector = null)
    {
        $result = $this->createChildInstance();

        if (isset($this->document) && $this->length > 0) {
            foreach ($this->nodes as $node) {
                $current = $node;
                while ($next = self::getNextElement($current)) {
                    $result->addDomNode($next);
                    $current = $next;
                }
            }

            if ($selector) {
                $result = $result->filter($selector);
            }
        }

        return $result;
    }

    /**
     * Returns DomQuery with immediately preceding sibling of all nodes
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function prev($selector = null)
    {
        $result = $this->createChildInstance();

        if (isset($this->document) && $this->length > 0) {
            foreach ($this->nodes as $node) { // get all previous sibling of all nodes
                if ($prev = self::getPreviousElement($node)) {
                    $result->addDomNode($prev);
                }
            }

            if ($selector) {
                $result = $result->filter($selector);
            }
        }

        return $result;
    }

    /**
     * Get all preceding siblings of each element in the set of matched elements, optionally filtered by a selector.
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that filters the set of matched elements
     *
     * @return self
     */
    public function prevAll($selector = null)
    {
        $result = $this->createChildInstance();

        if (isset($this->document) && $this->length > 0) {
            foreach ($this->nodes as $node) {
                $current = $node;
                while ($prev = self::getPreviousElement($current)) {
                    $result->addDomNode($prev, true);
                    $current = $prev;
                }
            }

            if ($selector) {
                $result = $result->filter($selector);
            }
        }

        return $result;
    }

    /**
     * Remove the set of matched elements
     *
     * @param  string|self|callable|\DOMNodeList|\DOMNode|null  $selector  expression that
     * filters the set of matched elements to be removed
     *
     * @return self
     */
    public function remove($selector = null)
    {
        $result = $this;
        if ($selector) {
            $result = $result->filter($selector);
        }
        foreach ($result->nodes as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }

        $result->nodes = array();
        $result->length = 0;

        return $result;
    }

    /**
     * Empty Dom
     *
     * @return $this
     */
    public function empty()
    {
        $this->remove();
        return $this;
    }

    /**
     * Check if is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->document);
    }

    /**
     * Import nodes and insert or append them via callback function
     *
     * @param  string|self|array  $content
     * @param  callable  $import_function
     *
     * @return \DOMNode[] $imported_nodes
     */
    private function importNodes($content, callable $import_function)
    {
        /* @var \DOMNode[] */
        $imported_nodes = [];

        if (\is_array($content)) {
            foreach ($content as $item) {
                $imported_nodes = array_merge($imported_nodes, $this->importNodes($item, $import_function));
            }
            return $imported_nodes;

        } else {

            if (\is_string($content) && strpos($content, "\n") !== false) {
                $this->preserve_no_newlines = false;
                if (isset($this->root_instance)) {
                    $this->root_instance->preserve_no_newlines = false;
                }
            }

            if ( ! ($content instanceof self)) {
                $content = new self($content);
            }

            foreach ($this->nodes as $node) {
                foreach ($content->getNodes() as $content_node) {
                    if ($content_node->ownerDocument === $node->ownerDocument) {
                        $imported_node = $content_node->cloneNode(true);
                    } else {
                        $imported_node = $this->document->importNode($content_node, true);
                    }
                    $imported_node = $import_function($node, $imported_node);
                    if ($imported_node instanceof \DOMNode) {
                        $imported_nodes[] = $imported_node;
                    }
                }
            }
        }

        return $imported_nodes;
    }

    /**
     * Get target result using selector or instance of self
     *
     * @param  string|self  $target
     * @param  string|self|\DOMNodeList|\DOMNode|\DOMDocument  $context
     *
     * @return self
     */
    private function getTargetResult($target, $context = null)
    {
        if ($context === null && \is_string($target) && strpos($target, '<') === false) {
            $context = $this->document;
        }

        return $context === null ? self::create($target) : self::create($target, $context);
    }

    /**
     * Insert content to the end of each element in the set of matched elements.
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function append()
    {
        $this->importNodes(\func_get_args(), function ($node, $imported_node) {
            /* @var \DOMNode $node */
            $node->appendChild($imported_node);
        });

        return $this;
    }

    /**
     * Insert every element in the set of matched elements to the end of the target.
     *
     * @param  string|self  $target
     *
     * @return self
     */
    public function appendTo($target)
    {
        $target_result = $this->getTargetResult($target);

        $nodes = $target_result->importNodes($this, function ($node, $imported_node) {
            /* @var \DOMNode $node */
            return $node->appendChild($imported_node);
        });

        $this->remove();

        return $target_result->find($nodes);
    }

    /**
     * Insert content to the beginning of each element in the set of matched elements
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function prepend()
    {
        $this->importNodes(\func_get_args(), function ($node, $imported_node) {
            /* @var \DOMNode $node */
            $node->insertBefore($imported_node, $node->childNodes->item(0));
        });

        return $this;
    }

    /**
     * Insert every element in the set of matched elements to the beginning of the target.
     *
     * @param  string|self  $target
     *
     * @return self
     */
    public function prependTo($target)
    {
        $target_result = $this->getTargetResult($target);

        $nodes = $target_result->importNodes($this, function ($node, $imported_node) {
            /* @var \DOMNode $node */
            return $node->insertBefore($imported_node, $node->childNodes->item(0));
        });

        $this->remove();

        return $target_result->find($nodes);
    }

    /**
     * Insert content before each element in the set of matched elements.
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function before()
    {
        $this->importNodes(\func_get_args(), function ($node, $imported_node) {
            if ($node->parentNode instanceof \DOMDocument) {
                throw new \Exception('Can not set before root element '.$node->tagName.' of document');
            }

            $node->parentNode->insertBefore($imported_node, $node);
        });

        return $this;
    }

    /**
     * Insert content after each element in the set of matched elements.
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function after()
    {
        $this->importNodes(\func_get_args(), function ($node, $imported_node) {
            if ($node->nextSibling) {
                $node->parentNode->insertBefore($imported_node, $node->nextSibling);
            } else { // node is last, so there is no next sibling to insert before
                $node->parentNode->appendChild($imported_node);
            }
        });

        return $this;
    }

    /**
     * Replace each element in the set of matched elements with the provided
     * new content and return the set of elements that was removed.
     *
     * @param  string|self  $new_content,...
     *
     * @return self
     */
    public function replaceWith()
    {
        $removed_nodes = new self();

        $this->importNodes(\func_get_args(), function ($node, $imported_node) use (&$removed_nodes) {
            if ($node->nextSibling) {
                $node->parentNode->insertBefore($imported_node, $node->nextSibling);
            } else { // node is last, so there is no next sibling to insert before
                $node->parentNode->appendChild($imported_node);
            }
            $removed_nodes->addDomNode($node);
            $node->parentNode->removeChild($node);
        });

        foreach (\func_get_args() as $new_content) {
            if ( ! \is_string($new_content)) {
                self::create($new_content)->remove();
            }
        }

        return $removed_nodes;
    }

    /**
     * Wrap an HTML structure around each element in the set of matched elements
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function wrap()
    {
        $this->importNodes(\func_get_args(), function ($node, $imported_node) {
            /* @var \DOMNode $imported_node */
            if ($node->parentNode instanceof \DOMDocument) {
                throw new \Exception('Can not wrap inside root element '.$node->tagName.' of document');
            }

            // replace node with imported wrapper
            $old = $node->parentNode->replaceChild($imported_node, $node);
            // old node goes inside the most inner child of wrapper
            $target = $imported_node;
            while ($target->hasChildNodes()) {
                $target = $target->childNodes[0];
            }
            $target->appendChild($old);
        });

        return $this;
    }

    /**
     * Wrap an HTML structure around all elements in the set of matched elements
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function wrapAll()
    {
        /* @var \DOMNode $wrapper_node */
        $wrapper_node = null; // node given as wrapper
        /* @var \DOMNode $wrap_target_node */
        $wrap_target_node = null; // node that wil be parent of content to be wrapped

        $this->importNodes(\func_get_args(), function ($node, $imported_node) use (&$wrapper_node, &$wrap_target_node) {
            /* @var \DOMNode $imported_node */
            if ($node->parentNode instanceof \DOMDocument) {
                throw new \Exception('Can not wrap inside root element '.$node->tagName.' of document');
            }
            if ($wrapper_node && $wrap_target_node) { // already wrapped before
                $old = $node->parentNode->removeChild($node);
                $wrap_target_node->appendChild($old);
            } else {
                $wrapper_node = $imported_node;
                // replace node with (imported) wrapper
                $old = $node->parentNode->replaceChild($imported_node, $node);
                // old node goes inside the most inner child of wrapper
                $target = $imported_node;

                while ($target->hasChildNodes()) {
                    $target = $target->childNodes[0];
                }
                $target->appendChild($old);
                $wrap_target_node = $target; // save for next round
            }
        });

        return $this;
    }

    /**
     * Wrap an HTML structure around the content of each element in the set of matched elements
     *
     * @param  string|self  $content,...
     *
     * @return $this
     */
    public function wrapInner()
    {
        foreach ($this->nodes as $node) {
            self::create($node->childNodes)->wrapAll(\func_get_args());
        }

        return $this;
    }

    /* @noinspection PhpDocMissingThrowsInspection */
    /**
     * Remove the parents of the set of matched elements from the DOM, leaving the matched elements in their place.
     *
     * @return DomQuery Parent node
     */
    public function unwrap()
    {
        $result = $this->createChildInstance();

        if ( ! isset($this->document) || $this->length <= 0) {
            return $result;
        }

        /** @var \DOMNode $parentNode */
        $parentNode = null;

        /** @var \DOMNode $node */
        foreach ($this->nodes as $node) {

            $current = $node;

            if ($node instanceof \DOMDocument) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw new \Exception('Can not unWrap the root element '.$current->tagName.' of document');
            }

            $parentNode = $current->parentNode;

            while ($current->hasChildNodes()) {
                /** @var \DOMNode $child */
                foreach ($current->childNodes as $child) {
                    $parentNode->appendChild($child);
                }
            }

            $parentNode->removeChild($node);
            $result->addDomNode($parentNode);
        }

        return $result;
    }

    /**
     * Check if property exist for this instance
     *
     * @param  string  $name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->__get($name) !== null;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'text':
            case 'plaintext':
                return $this->text();
                break;
            case 'html':
            case 'innerHTML':
                return $this->html();
                break;
            case 'outerHTML':
                return $this->getOuterHtml();
                break;
        }

        return parent::__get($name);
    }
}


// from DiDom - faster
class XpathQuery
{
    /**
     * Types of expression.
     *
     * @const string
     */
    public const TYPE_XPATH = 'XPATH';
    public const TYPE_CSS   = 'CSS';

    /**
     * @var array
     */
    protected static $compiled = [];

    /**
     * Converts a CSS selector into an XPath expression.
     *
     * @param string $expression XPath expression or CSS selector
     * @param string $type The type of the expression
     *
     * @return string XPath expression
     *
     * @throws InvalidSelectorException if the expression is empty
     */
    public static function compile($expression, $type = self::TYPE_CSS)
    {
        if (! is_string($expression)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($expression)));
        }

        if (! is_string($type)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 2 to be string, %s given', __METHOD__, gettype($type)));
        }

        if (strcasecmp($type, self::TYPE_XPATH) !== 0 && strcasecmp($type, self::TYPE_CSS) !== 0) {
            throw new RuntimeException(sprintf('Unknown expression type "%s"', $type));
        }

        $expression = trim($expression);

        if ($expression === '') {
            throw new InvalidSelectorException('The expression must not be empty');
        }

        if (strcasecmp($type, self::TYPE_XPATH) === 0) {
            return $expression;
        }

        if (! array_key_exists($expression, static::$compiled)) {
            static::$compiled[$expression] = static::cssToXpath($expression);
        }

        return static::$compiled[$expression];
    }

    /**
     * Converts a CSS selector into an XPath expression.
     *
     * @param string $selector A CSS selector
     * @param string $prefix Specifies the nesting of nodes
     *
     * @return string XPath expression
     *
     * @throws InvalidSelectorException
     */
    public static function cssToXpath($selector, $prefix = '//')
    {
        $paths = [];

        while ($selector !== '') {
            list($xpath, $selector) = static::parseAndConvertSelector($selector, $prefix);

            if (substr($selector, 0, 1) === ',') {
                $selector = trim($selector, ', ');
            }

            $paths[] = $xpath;
        }

        return implode('|', $paths);
    }

    /**
     * @param string $selector
     * @param string $prefix
     *
     * @return array
     *
     * @throws InvalidSelectorException
     */
    protected static function parseAndConvertSelector($selector, $prefix = '//')
    {
        if (substr($selector, 0, 1) === '>') {
            $prefix = '/';

            $selector = ltrim($selector, '> ');
        }

        $segments = self::getSegments($selector);
        $xpath = '';

        while (count($segments) > 0) {
            $xpath .= self::buildXpath($segments, $prefix);

            $selector = trim(substr($selector, strlen($segments['selector'])));
            $prefix = isset($segments['rel']) ? '/' : '//';

            if ($selector === '' || substr($selector, 0, 2) === '::' || substr($selector, 0, 1) === ',') {
                break;
            }

            $segments = self::getSegments($selector);
        }

        // if selector has property
        if (substr($selector, 0, 2) === '::') {
            $property = self::parseProperty($selector);
            $propertyXpath = self::convertProperty($property['name'], $property['args']);

            $selector = substr($selector, strlen($property['property']));
            $selector = trim($selector);

            $xpath .= '/' . $propertyXpath;
        }

        return [$xpath, $selector];
    }

    /**
     * @param string $selector
     *
     * @return array
     *
     * @throws InvalidSelectorException
     */
    protected static function parseProperty($selector)
    {
        $name = '(?P<name>[\w\-]+)';
        $args = '(?:\((?P<args>[^\)]+)?\))?';

        $regexp = '/^::' . $name . $args . '/is';

        if (preg_match($regexp, $selector, $matches) !== 1) {
            throw new InvalidSelectorException(sprintf('Invalid property "%s"', $selector));
        }

        $result = [];

        $result['property'] = $matches[0];
        $result['name'] = $matches['name'];
        $result['args'] = isset($matches['args']) ? explode(',', $matches['args']) : [];

        $result['args'] = array_map('trim', $result['args']);

        return $result;
    }

    /**
     * @param string $name
     * @param array $parameters
     *
     * @return string
     *
     * @throws InvalidSelectorException if the specified property is unknown
     */
    protected static function convertProperty($name, array $parameters = [])
    {
        if ($name === 'text') {
            return 'text()';
        }

        if ($name === 'attr') {
            if (count($parameters) === 0) {
                return '@*';
            }

            $attributes = [];

            foreach ($parameters as $attribute) {
                $attributes[] = sprintf('name() = "%s"', $attribute);
            }

            return sprintf('@*[%s]', implode(' or ', $attributes));
        }

        throw new InvalidSelectorException(sprintf('Unknown property "%s"', $name));
    }

    /**
     * Converts a CSS pseudo-class into an XPath expression.
     *
     * @param string $pseudo Pseudo-class
     * @param string $tagName
     * @param array $parameters
     *
     * @return string
     *
     * @throws InvalidSelectorException if the specified pseudo-class is unknown
     */
    protected static function convertPseudo($pseudo, &$tagName, array $parameters = [])
    {
        switch ($pseudo) {
            case 'root':
                return 'not(parent::*)';
            case 'first-child':
                return 'position() = 1';
            case 'last-child':
                return 'position() = last()';
            case 'nth-child':
                $xpath = sprintf('(name()="%s") and (%s)', $tagName, self::convertNthExpression($parameters[0]));
                $tagName = '*';

                return $xpath;
            case 'contains':
                $string = trim($parameters[0], '\'"');

                if (count($parameters) === 1) {
                    return self::convertContains($string);
                }

                if ($parameters[1] !== 'true' && $parameters[1] !== 'false') {
                    throw new InvalidSelectorException(sprintf('Parameter 2 of "contains" pseudo-class must be equal true or false, "%s" given', $parameters[1]));
                }

                $caseSensitive = $parameters[1] === 'true';

                if (count($parameters) === 2) {
                    return self::convertContains($string, $caseSensitive);
                }

                if ($parameters[2] !== 'true' && $parameters[2] !== 'false') {
                    throw new InvalidSelectorException(sprintf('Parameter 3 of "contains" pseudo-class must be equal true or false, "%s" given', $parameters[2]));
                }

                $fullMatch = $parameters[2] === 'true';

                return self::convertContains($string, $caseSensitive, $fullMatch);
            case 'has':
                return self::cssToXpath($parameters[0], './/');
            case 'not':
                return sprintf('not(self::%s)', self::cssToXpath($parameters[0], ''));

            case 'nth-of-type':
                return self::convertNthExpression($parameters[0]);
            case 'empty':
                return 'count(descendant::*) = 0';
            case 'not-empty':
                return 'count(descendant::*) > 0';
        }

        throw new InvalidSelectorException(sprintf('Unknown pseudo-class "%s"', $pseudo));
    }

    /**
     * @param array $segments
     * @param string $prefix Specifies the nesting of nodes
     *
     * @return string XPath expression
     *
     * @throws InvalidArgumentException if you neither specify tag name nor attributes
     */
    public static function buildXpath(array $segments, $prefix = '//')
    {
        $tagName = isset($segments['tag']) ? $segments['tag'] : '*';

        $attributes = [];

        // if the id attribute specified
        if (isset($segments['id'])) {
            $attributes[] = sprintf('@id="%s"', $segments['id']);
        }

        // if the class attribute specified
        if (isset($segments['classes'])) {
            foreach ($segments['classes'] as $class) {
                $attributes[] = sprintf('contains(concat(" ", normalize-space(@class), " "), " %s ")', $class);
            }
        }

        // if the attributes specified
        if (isset($segments['attributes'])) {
            foreach ($segments['attributes'] as $name => $value) {
                $attributes[] = self::convertAttribute($name, $value);
            }
        }

        // if the pseudo class specified
        if (array_key_exists('pseudo', $segments)) {
            foreach ($segments['pseudo'] as $pseudo) {
                $expression = $pseudo['expression'] !== null ? $pseudo['expression'] : '';

                $parameters = explode(',', $expression);
                $parameters = array_map('trim', $parameters);

                $attributes[] = self::convertPseudo($pseudo['type'], $tagName, $parameters);
            }
        }

        if (count($attributes) === 0 && ! isset($segments['tag'])) {
            throw new InvalidArgumentException('The array of segments must contain the name of the tag or at least one attribute');
        }

        $xpath = $prefix . $tagName;

        if ($count = count($attributes)) {
            $xpath .= ($count > 1) ? sprintf('[(%s)]', implode(') and (', $attributes)) : sprintf('[%s]', $attributes[0]);
        }

        return $xpath;
    }

    /**
     * @param string $name The name of an attribute
     * @param string $value The value of an attribute
     *
     * @return string
     */
    protected static function convertAttribute($name, $value)
    {
        $isSimpleSelector = ! in_array(substr($name, 0, 1), ['^', '!'], true);
        $isSimpleSelector = $isSimpleSelector && (! in_array(substr($name, -1), ['^', '$', '*', '!', '~'], true));

        if ($isSimpleSelector) {
            // if specified only the attribute name
            $xpath = $value === null ? '@' . $name : sprintf('@%s="%s"', $name, $value);

            return $xpath;
        }

        // if the attribute name starts with ^
        // example: *[^data-]
        if (substr($name, 0, 1) === '^') {
            $xpath = sprintf('@*[starts-with(name(), "%s")]', substr($name, 1));

            return $value === null ? $xpath : sprintf('%s="%s"', $xpath, $value);
        }

        // if the attribute name starts with !
        // example: input[!disabled]
        if (substr($name, 0, 1) === '!') {
            $xpath = sprintf('not(@%s)', substr($name, 1));

            return $xpath;
        }

        $symbol = substr($name, -1);
        $name = substr($name, 0, -1);

        switch ($symbol) {
            case '^':
                $xpath = sprintf('starts-with(@%s, "%s")', $name, $value);

                break;
            case '$':
                $xpath = sprintf('substring(@%s, string-length(@%s) - string-length("%s") + 1) = "%s"', $name, $name, $value, $value);

                break;
            case '*':
                $xpath = sprintf('contains(@%s, "%s")', $name, $value);

                break;
            case '!':
                $xpath = sprintf('not(@%s="%s")', $name, $value);

                break;
            case '~':
                $xpath = sprintf('contains(concat(" ", normalize-space(@%s), " "), " %s ")', $name, $value);

                break;
        }

        return $xpath;
    }

    /**
     * Converts nth-expression into an XPath expression.
     *
     * @param string $expression nth-expression
     *
     * @return string
     *
     * @throws InvalidSelectorException if the given nth-child expression is empty or invalid
     */
    protected static function convertNthExpression($expression)
    {
        if ($expression === '') {
            throw new InvalidSelectorException('nth-child (or nth-last-child) expression must not be empty');
        }

        if ($expression === 'odd') {
            return 'position() mod 2 = 1 and position() >= 1';
        }

        if ($expression === 'even') {
            return 'position() mod 2 = 0 and position() >= 0';
        }

        if (is_numeric($expression)) {
            return sprintf('position() = %d', $expression);
        }

        if (preg_match("/^(?P<mul>[0-9]?n)(?:(?P<sign>\+|\-)(?P<pos>[0-9]+))?$/is", $expression, $segments)) {
            if (isset($segments['mul'])) {
                $multiplier = $segments['mul'] === 'n' ? 1 : trim($segments['mul'], 'n');
                $sign = (isset($segments['sign']) && $segments['sign'] === '+') ? '-' : '+';
                $position = isset($segments['pos']) ? $segments['pos'] : 0;

                return sprintf('(position() %s %d) mod %d = 0 and position() >= %d', $sign, $position, $multiplier, $position);
            }
        }

        throw new InvalidSelectorException(sprintf('Invalid nth-child expression "%s"', $expression));
    }

    /**
     * @param string $string
     * @param bool $caseSensitive
     * @param bool $fullMatch
     *
     * @return string
     */
    protected static function convertContains($string, $caseSensitive = true, $fullMatch = false)
    {
        if ($caseSensitive && $fullMatch) {
            return sprintf('text() = "%s"', $string);
        }

        if ($caseSensitive && ! $fullMatch) {
            return sprintf('contains(text(), "%s")', $string);
        }

        $strToLowerFunction = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';

        if (! $caseSensitive && $fullMatch) {
            return sprintf("php:functionString(\"{$strToLowerFunction}\", .) = php:functionString(\"{$strToLowerFunction}\", \"%s\")", $string);
        }

        // if ! $caseSensitive and ! $fullMatch
        return sprintf("contains(php:functionString(\"{$strToLowerFunction}\", .), php:functionString(\"{$strToLowerFunction}\", \"%s\"))", $string);
    }

    /**
     * Splits the CSS selector into parts (tag name, ID, classes, attributes, pseudo-class).
     *
     * @param string $selector CSS selector
     *
     * @return array
     *
     * @throws InvalidSelectorException if the selector is empty or not valid
     */
    public static function getSegments($selector)
    {
        $selector = trim($selector);

        if ($selector === '') {
            throw new InvalidSelectorException('The selector must not be empty.');
        }

        $pregMatchResult = preg_match(self::getSelectorRegex(), $selector, $segments);

        if ($pregMatchResult === false || $pregMatchResult === 0 || $segments[0] === '') {
            throw new InvalidSelectorException(sprintf('Invalid selector "%s".', $selector));
        }

        $result = ['selector' => $segments[0]];

        if (isset($segments['tag']) && $segments['tag'] !== '') {
            $result['tag'] = $segments['tag'];
        }

        // if the id attribute specified
        if (isset($segments['id']) && $segments['id'] !== '') {
            $result['id'] = $segments['id'];
        }

        // if the attributes specified
        if (isset($segments['attrs'])) {
            $attributes = trim($segments['attrs'], '[]');
            $attributes = explode('][', $attributes);

            foreach ($attributes as $attribute) {
                if ($attribute !== '') {
                    list($name, $value) = array_pad(explode('=', $attribute, 2), 2, null);

                    if ($name === '') {
                        throw new InvalidSelectorException(sprintf('Invalid selector "%s": attribute name must not be empty', $selector));
                    }

                    // equal null if specified only the attribute name
                    $result['attributes'][$name] = is_string($value) ? trim($value, '\'"') : null;
                }
            }
        }

        // if the class attribute specified
        if (isset($segments['classes'])) {
            $classes = trim($segments['classes'], '.');
            $classes = explode('.', $classes);

            foreach ($classes as $class) {
                if ($class !== '') {
                    $result['classes'][] = $class;
                }
            }
        }

        // if the pseudo class specified
        if (isset($segments['pseudo']) && $segments['pseudo'] !== '') {
            preg_match_all('/:(?P<type>[\w\-]+)(?:\((?P<expr>[^\)]+)\))?/', $segments['pseudo'], $pseudoClasses);

            $result['pseudo'] = [];

            foreach ($pseudoClasses['type'] as $index => $pseudoType) {
                $result['pseudo'][] = [
                    'type' => $pseudoType,
                    'expression' => $pseudoClasses['expr'][$index] !== '' ? $pseudoClasses['expr'][$index] : null,
                ];
            }
        }

        // if it is a direct descendant
        if (isset($segments['rel'])) {
            $result['rel'] = $segments['rel'];
        }

        return $result;
    }

    private static function getSelectorRegex()
    {
        $tag = '(?P<tag>[\*|\w|\-]+)?';
        $id = '(?:#(?P<id>[\w|\-]+))?';
        $classes = '(?P<classes>\.[\w|\-|\.]+)*';
        $attrs = '(?P<attrs>(?:\[.+?\])*)?';
        $pseudoType = '[\w\-]+';
        $pseudoExpr = '(?:\([^\)]+\))?';
        $pseudo = '(?P<pseudo>(?::' . $pseudoType  . $pseudoExpr . ')+)?';
        $rel = '\s*(?P<rel>>)?';

        return '/' . $tag . $id . $classes . $attrs . $pseudo . $rel . '/is';
    }

    /**
     * @return array
     */
    public static function getCompiled()
    {
        return static::$compiled;
    }

    /**
     * @param array $compiled
     *
     * @throws InvalidArgumentException if the attributes is not an array
     */
    public static function setCompiled(array $compiled)
    {
        static::$compiled = $compiled;
    }
}
