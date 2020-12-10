
<div class="form-group">
    <div class="row" {!! $attributes !!} >
        <div class="col-sm-12" >
            <h{{$no}} class="field-header">
                @if($escape)
                    {{ $label }}
                @else
                    {!! $label !!}
                @endif
            </h{{$no}}>
        </div>
    </div>

    @if($hr)
    <hr style="margin: 0px 15px;" />
    @endif
</div>

