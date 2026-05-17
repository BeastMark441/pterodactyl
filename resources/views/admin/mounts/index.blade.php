@extends('layouts.admin')

@section('title')
    Точки монтирования
@endsection

@section('content-header')
    <h1>Точки монтирования<small>Настройка и управление дополнительными точками монтирования для серверов.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li class="active">Точки монтирования</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Список точек монтирования</h3>

                    <div class="box-tools">
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newMountModal">Создать новую</button>
                    </div>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Источник</th>
                                <th>Цель</th>
                                <th class="text-center">Eggs</th>
                                <th class="text-center">Ноды</th>
                                <th class="text-center">Сервера</th>
                            </tr>

                            @foreach ($mounts as $mount)
                                <tr>
                                    <td><code>{{ $mount->id }}</code></td>
                                    <td><a href="{{ route('admin.mounts.view', $mount->id) }}">{{ $mount->name }}</a></td>
                                    <td><code>{{ $mount->source }}</code></td>
                                    <td><code>{{ $mount->target }}</code></td>
                                    <td class="text-center">{{ $mount->eggs_count }}</td>
                                    <td class="text-center">{{ $mount->nodes_count }}</td>
                                    <td class="text-center">{{ $mount->servers_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newMountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.mounts') }}" method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="color: #FFFFFF">&times;</span>
                        </button>

                        <h4 class="modal-title">Создать точку монтирования</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="pName" class="form-label">Название</label>
                                <input type="text" id="pName" name="name" class="form-control" />
                                <p class="text-muted small">Уникальное имя, используемое для отличия этой точки монтирования от других.</p>
                            </div>

                            <div class="col-md-12">
                                <label for="pDescription" class="form-label">Описание</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="4"></textarea>
                                <p class="text-muted small">Более подробное описание этой точки монтирования, должно быть короче 191 символа.</p>
                            </div>

                            <div class="col-md-6">
                                <label for="pSource" class="form-label">Источник</label>
                                <input type="text" id="pSource" name="source" class="form-control" />
                                <p class="text-muted small">Путь к файлу на хост-системе для монтирования в контейнер.</p>
                            </div>

                            <div class="col-md-6">
                                <label for="pTarget" class="form-label">Цель</label>
                                <input type="text" id="pTarget" name="target" class="form-control" />
                                <p class="text-muted small">Место, где точка монтирования будет доступна внутри контейнера.</p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Только чтение</label>

                                <div>
                                    <div class="radio radio-success radio-inline">
                                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" checked>
                                        <label for="pReadOnlyFalse">Нет</label>
                                    </div>

                                    <div class="radio radio-warning radio-inline">
                                        <input type="radio" id="pReadOnly" name="read_only" value="1">
                                        <label for="pReadOnly">Да</label>
                                    </div>
                                </div>

                                <p class="text-muted small">Будет ли точка монтирования доступна только для чтения внутри контейнера?</p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Доступно пользователю</label>

                                <div>
                                    <div class="radio radio-success radio-inline">
                                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" checked>
                                        <label for="pUserMountableFalse">Нет</label>
                                    </div>

                                    <div class="radio radio-warning radio-inline">
                                        <input type="radio" id="pUserMountable" name="user_mountable" value="1">
                                        <label for="pUserMountable">Да</label>
                                    </div>
                                </div>

                                <p class="text-muted small">Должны ли пользователи иметь возможность подключать эту точку монтирования самостоятельно?</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        {!! csrf_field() !!}
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success btn-sm">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection