@extends('kit::backend.layouts.modal')
@section('content')
    {!! Form::model($enum,['url' => $url, 'method' => $method]) !!}
    <div class="form-group{{ $errors->has('title') ? ' has-error':'' }}">
        {!! Form::label('label', trans('enum::common.title'), ['class' => 'control-label']) !!}
        {!! Form::text('title', null, ['class' => 'has-slug form-control','data-slug_target' => "#slug"]) !!}
        @if($errors->has('title'))
            <p class="help-block">{{ $errors->first('title') }}</p>
        @endif
    </div>
    <div class="form-group{{ $errors->has('slug') ? ' has-error':'' }}">
        {!! Form::label('slug', trans('enum::common.slug'), ['class' => 'control-label']) !!}
        {!! Form::text('slug', null, ['class' => 'form-control text-navy', 'id' => 'slug']) !!}
        @if($errors->has('slug'))
            <p class="help-block">{{ $errors->first('slug') }}</p>
        @endif
    </div>
    <div class="form-group{{ $errors->has('params') ? ' has-error':'' }}">
        {!! Form::label('label', trans('enum::common.params'), ['class' => 'control-label']) !!}
        {!! Form::text('params', null, ['class' => 'form-control']) !!}
        @if($errors->has('params'))
            <p class="help-block">{{ $errors->first('params') }}</p>
        @endif
    </div>
    {!! Form::close() !!}
@stop