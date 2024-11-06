@extends('layouts.admin')

@section('title')
    Сервер — {{ $server->name }}: Удаление
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>Удалить этот сервер из панели.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li><a href="{{ route('admin.servers') }}">Серверы</a></li>
        <li><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></li>
        <li class="active">Удалить</li>
    </ol>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Безопасное удаление сервера</h3>
            </div>
            <div class="box-body">
                <p>Это действие попытается удалить сервер как из панели, так и из демона. Если любой из них сообщит об ошибке, действие будет отменено.</p>
                <p class="text-danger small">Удаление сервера - необратимое действие. <strong>Все данные сервера</strong> (включая файлы и пользователей) будут удалены из системы.</p>
            </div>
            <div class="box-footer">
                <form id="deleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <button id="deletebtn" class="btn btn-danger">Безопасно удалить этот сервер</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Принудительное удаление сервера</h3>
            </div>
            <div class="box-body">
                <p>Это действие попытается удалить сервер как из панели, так и из демона. Если демон не ответит или сообщит об ошибке, удаление продолжится.</p>
                <p class="text-danger small">Удаление сервера - необратимое действие. <strong>Все данные сервера</strong> (включая файлы и пользователей) будут удалены из системы.</p>
            </div>
            <div class="box-footer">
                <form id="forcedeleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="force_delete" value="1" />
                    <button id="forcedeletebtn" class="btn btn-danger">Принудительно удалить этот сервер</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#deletebtn').click(function (event) {
        event.preventDefault();
        swal({
            title: '',
            type: 'warning',
            text: 'Вы уверены, что хотите удалить этот сервер? Пути назад нет, все данные будут немедленно удалены.',
            showCancelButton: true,
            confirmButtonText: 'Удалить',
            confirmButtonColor: '#d9534f',
            closeOnConfirm: false
        }, function () {
            $('#deleteform').submit()
        });
    });

    $('#forcedeletebtn').click(function (event) {
        event.preventDefault();
        swal({
            title: '',
            type: 'warning',
            text: 'Вы уверены, что хотите удалить этот сервер? Пути назад нет, все данные будут немедленно удалены.',
            showCancelButton: true,
            confirmButtonText: 'Удалить',
            confirmButtonColor: '#d9534f',
            closeOnConfirm: false
        }, function () {
            $('#forcedeleteform').submit()
        });
    });
    </script>
@endsection
