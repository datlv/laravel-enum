@extends($layout)
@section('content')
    <div class="panel panel-default panel-table panel-sidebar">
        <div class="panel-heading clearfix">
            <div class="loading hidden"></div>
            <div class="buttons pull-right">
                <a href="{{route($route_prefix.'backend.enum.create')}}"
                   class="modal-link btn btn-success btn-xs{{$readOnly ? ' disabled': ''}}"
                   data-title="{{trans('common.create_object', ['name' => $typeName])}}"
                   data-label="{{trans('common.save')}}"
                   data-width="small"
                   data-icon="align-justify">
                    <span class="glyphicon glyphicon-plus-sign"></span> {{trans('common.add_object', ['name' => $typeName])}}
                </a>
            </div>
        </div>
        <div class="panel-body">
            <div class="row row-height">
                <div class="row-height-inside">
                    <div class="col-xs-9 col-height">
                        <div class="panel-body-content left">
                            <table id="enum-manage" class="table table-striped table-hover table-force-bordered">
                                <thead>
                                <tr>
                                    <th class="min-width text-right">#</th>
                                    <th>{{trans('enum::common.title')}}</th>
                                    <th class="min-width">{{trans('enum::common.slug')}}</th>
                                    <th class="min-width">{{trans('enum::common.params')}}</th>
                                    <th class="min-width">{{trans('common.actions')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($enums as $i => $enum)
                                    <tr id="row-{{$enum->id}}">
                                        <td class="min-width text-right">{{$i +1}}</td>
                                        <td>{{$enum->title}}</td>
                                        <td class="min-width">{{$enum->slug}}</td>
                                        <td class="min-width"><code>{{$enum->params}}</code></td>
                                        <td class="min-width">
                                            @if($readOnly)
                                                {!! Html::linkButton(
                                                    '#',
                                                    null,
                                                    ['type' => 'info', 'size' => 'xs', 'icon' => 'edit', 'class' => 'disabled']
                                                ) !!}
                                            @else
                                                {!! Html::modalButton(
                                                    route($route_prefix.'backend.enum.edit', ['enum' => $enum->id]),
                                                    null,
                                                    [
                                                        'toggle' => 'tooltip',
                                                        'icon' => 'fa-list',
                                                        'label' => trans('common.save_changes'),
                                                        'title' => trans('common.update_object', ['name' => $typeName]),
                                                        'width' => 'small'
                                                    ],
                                                    ['type' => 'info', 'size' => 'xs', 'icon' => 'edit']
                                                ) !!}
                                            @endif
                                            @if($enum->isUsed() || $readOnly)
                                                {!! Html::linkButton(
                                                    '#',
                                                    null,
                                                    ['type' => 'danger', 'size' => 'xs', 'icon' => 'trash', 'class' => 'disabled']
                                                ) !!}
                                            @else
                                                {!! Html::linkButton(
                                                    route($route_prefix.'backend.enum.destroy', ['enum' => $enum->id]),
                                                    null,
                                                    ['type' => 'danger', 'size' => 'xs', 'icon' => 'trash', 'class' => 'delete-enum'],
                                                    [
                                                        'toggle' => 'tooltip',
                                                        'title' => trans('common.delete_object', ['name' => $typeName])
                                                    ]
                                                ) !!}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if($readOnly)
                                <div class="alert alert-warning"><em>{!! $readOnly !!}</em></div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xs-3 col-height panel-body-sidebar right">
                        @foreach($types as $model => $list)
                            <div class="group-title">{{$model}}</div>
                            <ul class="nav nav-tabs tabs-right">
                                @foreach($list as $type => $title)
                                    <li{!! $current ==$type ? ' class="active"':'' !!}>
                                        <a href="{{route($route_prefix.'backend.enum.type', ['type' =>$type])}}">{{$title}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
        <div class="panel-footer">
            <span class="glyphicon glyphicon-info-sign"></span> {{ trans('enum::common.order_hint')}}
        </div>
    </div>
@stop

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $.fn.mbHelpers.reloadPage = function () {
            location.reload(true);
        };
                @if(!$readOnly)
        var enum_manage = $("#enum-manage");
        enum_manage.rowReorder({
            url: '{{route($route_prefix.'backend.enum.order')}}',
            containment: '#enum-manage',
            loading: '.loading'
        });
        $('a.delete-enum', enum_manage).click(function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url != '#') {
                window.bootbox.confirm({
                    message: "<div class=\"message-delete\"><div class=\"confirm\">{!!trans('enum::common.delete_confirm', ['name' => "<strong>$typeName</strong>"])!!}</div></div>",
                    title: '{{trans('common.delete_object', ['name' => $typeName])}}' + '?',
                    buttons: {
                        cancel: {label: '{{trans('common.cancel')}}', className: "btn-default btn-white"},
                        confirm: {label: '{{trans('common.ok')}}', className: "btn-danger"}
                    },
                    callback: function (ok) {
                        if (ok) {
                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                dataType: 'json',
                                data: {_token: window.Laravel.csrfToken},
                                success: function (message) {
                                    $.fn.mbHelpers.showMessage(message.type, message.content);
                                    $.fn.mbHelpers.reloadPage();
                                }
                            });
                        }
                    }
                });
            }
        });
        @endif
    });
</script>
@endpush