@if ($errors && $errors->has($element_name))
<span class="invalid-feedback" role="alert">
  <strong>{{ $errors->first($element_name) }}</strong>
</span>
@endif