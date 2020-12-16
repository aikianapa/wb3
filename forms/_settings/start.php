<html>
<div class="m-3">
    <div class="row">
        <wb-data wb-tree="{'table':'_settings','item':'settings','field':'cmsmenu','branch':'aside->forms','parent':'false'}">
            <div class="col-6 col-sm-4 col-md-3 mb-3">
                <div class="card">
                    <div class="card-body" data-ajax="{{data.ajax}}">
                        <h5 class="card-title text-primary">{{data.label}}</h5>
                        <p class="card-text text-center text-secondary">
                            <wb-var icon="{{data.icon}}" wb-if='"{{data.icon}}">""' />
                            <wb-var icon="ri-sticky-note-line" wb-if='"{{data.icon}}"==""' />
                            <i class="{{_var.icon}}" style='font-size:10vw'></i>&nbsp;&nbsp;
                        </p>
                    </div>
                </div>
            </div>
        </wb-data>
        <wb-data wb-tree="{'table':'_settings','item':'settings','field':'cmsmenu','branch':'aside->settings','parent':'false'}">
            <div class="col-6 col-sm-4 col-md-3 mb-3">
                <div class="card">
                    <div class="card-body" data-ajax="{{data.ajax}}">
                        <h5 class="card-title text-primary">{{data.label}}</h5>
                        <p class="card-text text-center text-secondary">
                            <wb-var icon="{{data.icon}}" wb-if='"{{data.icon}}">""' />
                            <wb-var icon="ri-sticky-note-line" wb-if='"{{data.icon}}"==""' />
                            <i class="{{_var.icon}}" style='font-size:10vw'></i>&nbsp;&nbsp;
                        </p>
                    </div>
                </div>
            </div>
        </wb-data>
    </div>
</div>
</html>