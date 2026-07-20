@if(count($errors) > 0)
    @foreach($errors->all as $error)
        <div class="alert alert-danger"  style="font-size:35px; font-family:Georgia, 'Times New Roman', Times, serif;">
           <h3>
            {{ error }}
           </h3>
        </div>        
    @endforeach
@endif

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif