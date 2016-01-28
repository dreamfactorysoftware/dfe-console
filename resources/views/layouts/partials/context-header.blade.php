<?php
/**
 * @var string $title
 */
?>
<div class="nav dfe-section-header">
    <h4>@if(!isset($noSpinner)){!! '<i class="fa fa-fw fa-spinner label-spinner hidden"></i>' !!}@endif{{ $title }}</h4>
</div>

@if (count($errors) > 0)
    <!-- Form Error List -->
<div class="alert {{ Session::get('alert-context', 'alert-danger') }}">
    <strong>There was an error with your request.</strong>
    <br /><br />
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
