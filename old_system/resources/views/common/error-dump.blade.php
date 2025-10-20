@if(Session('success'))
    <div class="alert alert-info">{!! Session('success') !!}</div>
@endif

@if(Session('warning'))
    <div class="alert alert-warning">{!! Session('warning') !!}</div>
@endif

@if(Session('error'))
    <div class="alert alert-danger">{!! Session('error') !!}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif