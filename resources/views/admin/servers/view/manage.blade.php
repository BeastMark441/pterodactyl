@extends('layouts.admin')

@section('title')
    Сервер — {{ $server->name }}: Управление
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>Дополнительные действия для управления этим сервером.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li><a href="{{ route('admin.servers') }}">Сервера</a></li>
        <li><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></li>
        <li class="active">Управление</li>
    </ol>
@endsection

@section('content')
    @include('admin.servers.partials.navigation')
    <div class="row equal-height">
        <div class="col-sm-4">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Переустановить сервер</h3>
                </div>
                <div class="box-body">
                    <p>Это переустановит сервер с назначенными сервисными скриптами. <strong>Опасно!</strong> Это может перезаписать данные сервера.</p>
                </div>
                <div class="box-footer">
                    @if($server->isInstalled())
                        <form action="{{ route('admin.servers.view.manage.reinstall', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <button type="submit" class="btn btn-danger">Переустановить сервер</button>
                        </form>
                    @else
                        <button class="btn btn-danger disabled">Сервер должен быть корректно установлен для переустановки</button>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Статус установки</h3>
                </div>
                <div class="box-body">
                    <p>Если вам нужно изменить статус установки с «не установлен» на «установлен» или наоборот, вы можете сделать это кнопкой ниже.</p>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.servers.view.manage.toggle', $server->id) }}" method="POST">
                        {!! csrf_field() !!}
                        <button type="submit" class="btn btn-primary">Переключить статус установки</button>
                    </form>
                </div>
            </div>
        </div>

        @if(! $server->isSuspended())
            <div class="col-sm-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Приостановить сервер</h3>
                    </div>
                    <div class="box-body">
                        <p>Это приостановит сервер, остановит все запущенные процессы и немедленно заблокирует пользователю доступ к файлам и управлению сервером через панель или API.</p>
                    </div>
                    <div class="box-footer">
                        <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" value="suspend" />
                            <button type="submit" class="btn btn-warning @if(! is_null($server->transfer)) disabled @endif">Приостановить сервер</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Снять приостановку</h3>
                    </div>
                    <div class="box-body">
                        <p>Это снимет приостановку с сервера и восстановит обычный доступ пользователя.</p>
                    </div>
                    <div class="box-footer">
                        <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" value="unsuspend" />
                            <button type="submit" class="btn btn-success">Снять приостановку</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if(is_null($server->transfer))
            <div class="col-sm-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Перенести сервер</h3>
                    </div>
                    <div class="box-body">
                        <p>
                            Перенести этот сервер на другую ноду, подключённую к этой панели.
                            <strong>Внимание!</strong> Эта функция ещё не полностью протестирована и может содержать ошибки.
                        </p>
                    </div>

                    <div class="box-footer">
                        @if($canTransfer)
                            <button class="btn btn-success" data-toggle="modal" data-target="#transferServerModal">Перенести сервер</button>
                        @else
                            <button class="btn btn-success disabled">Перенести сервер</button>
                            <p style="padding-top: 1rem;">Для переноса сервера в панели должно быть настроено более одной ноды.</p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Перенести сервер</h3>
                    </div>
                    <div class="box-body">
                        <p>
                            Этот сервер в данный момент переносится на другую ноду.
                            Перенос был запущен <strong>{{ $server->transfer->created_at }}</strong>
                        </p>
                    </div>

                    <div class="box-footer">
                        <button class="btn btn-success disabled">Перенести сервер</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="transferServerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.servers.view.manage.transfer', $server->id) }}" method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Перенести сервер</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="pNodeId">Нода</label>
                                <select name="node_id" id="pNodeId" class="form-control">
                                    @foreach($locations as $location)
                                        <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                            @foreach($location->nodes as $node)

                                                @if($node->id != $server->node_id)
                                                    <option value="{{ $node->id }}"
                                                            @if($location->id === old('location_id')) selected @endif
                                                    >{{ $node->name }}</option>
                                                @endif

                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <p class="small text-muted no-margin">Нода, на которую будет перенесён этот сервер.</p>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="pAllocation">Основной порт</label>
                                <select name="allocation_id" id="pAllocation" class="form-control"></select>
                                <p class="small text-muted no-margin">Основной порт, который будет назначён этому серверу.</p>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="pAllocationAdditional">Дополнительные порты</label>
                                <select name="allocation_additional[]" id="pAllocationAdditional" class="form-control" multiple></select>
                                <p class="small text-muted no-margin">Дополнительные порты, которые будут назначены этому серверу при создании.</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        {!! csrf_field() !!}
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success btn-sm">Подтвердить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}

    @if($canTransfer)
        {!! Theme::js('js/admin/server/transfer.js') !!}
    @endif
@endsection