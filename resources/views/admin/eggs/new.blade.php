@extends('layouts.admin')

@section('title')
    Гнезда &rarr; Новое Яйцо
@endsection

@section('content-header')
    <h1>Новое Яйцо<small>Создайте новое Яйцо для назначения серверам.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li><a href="{{ route('admin.nests') }}">Гнезда</a></li>
        <li class="active">Новое Яйцо</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.nests.egg.new') }}" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Конфигурация</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pNestId" class="form-label">Связанное Гнездо</label>
                                <div>
                                    <select name="nest_id" id="pNestId">
                                        @foreach($nests as $nest)
                                            <option value="{{ $nest->id }}" {{ old('nest_id') != $nest->id ?: 'selected' }}>{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small">Думайте о Гнезде как о категории. Вы можете поместить несколько Яиц в одно гнездо, но старайтесь помещать только связанные Яйца в каждое Гнездо.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pName" class="form-label">Имя</label>
                                <input type="text" id="pName" name="name" value="{{ old('name') }}" class="form-control" />
                                <p class="text-muted small">Простое, понятное имя для использования в качестве идентификатора для этого Яйца. Это то, что пользователи увидят как тип своего игрового сервера.</p>
                            </div>
                            <div class="form-group">
                                <label for="pDescription" class="form-label">Описание</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="8">{{ old('description') }}</textarea>
                                <p class="text-muted small">Описание этого Яйца.</p>
                            </div>
                            <div class="form-group">
                                <div class="checkbox checkbox-primary no-margin-bottom">
                                    <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1" {{ \Pterodactyl\Helpers\Utilities::checked('force_outgoing_ip', 0) }} />
                                    <label for="pForceOutgoingIp" class="strong">Принудительный Исходящий IP</label>
                                    <p class="text-muted small">
                                        Принуждает весь исходящий сетевой трафик иметь исходный IP, NATed к основному IP-адресу выделения сервера.
                                        Необходимо для правильной работы некоторых игр, когда у Узла несколько публичных IP-адресов.
                                        <br>
                                        <strong>
                                            Включение этой опции отключит внутреннюю сеть для любых серверов, использующих это яйцо,
                                            что приведет к невозможности внутреннего доступа к другим серверам на том же узле.
                                        </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pDockerImage" class="control-label">Docker Образы</label>
                                <textarea id="pDockerImages" name="docker_images" rows="4" placeholder="quay.io/pterodactyl/service" class="form-control">{{ old('docker_images') }}</textarea>
                                <p class="text-muted small">Docker образы, доступные для серверов, использующих это яйцо. Введите один на строку. Пользователи смогут выбрать из этого списка образов, если указано более одного значения.</p>
                            </div>
                            <div class="form-group">
                                <label for="pStartup" class="control-label">Команда Запуска</label>
                                <textarea id="pStartup" name="startup" class="form-control" rows="10">{{ old('startup') }}</textarea>
                                <p class="text-muted small">Команда запуска по умолчанию, которая должна использоваться для новых серверов, созданных с этим Яйцом. Вы можете изменить это для каждого сервера по мере необходимости.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Управление Процессами</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="alert alert-warning">
                                <p>Все поля обязательны для заполнения, если вы не выберете отдельный вариант из выпадающего списка 'Копировать Настройки Из', в этом случае поля могут быть оставлены пустыми для использования значений из этого варианта.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFrom" class="form-label">Копировать Настройки Из</label>
                                <select name="config_from" id="pConfigFrom" class="form-control">
                                    <option value="">Нет</option>
                                </select>
                                <p class="text-muted small">Если вы хотите использовать настройки по умолчанию из другого Яйца, выберите его из выпадающего списка выше.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStop" class="form-label">Команда Остановки</label>
                                <input type="text" id="pConfigStop" name="config_stop" class="form-control" value="{{ old('config_stop') }}" />
                                <p class="text-muted small">Команда, которая должна быть отправлена серверным процессам для их корректного завершения. Если вам нужно отправить <code>SIGINT</code>, введите здесь <code>^C</code>.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigLogs" class="form-label">Конфигурация Логов</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs" class="form-control" rows="6">{{ old('config_logs') }}</textarea>
                                <p class="text-muted small">Это должно быть JSON-представление того, где хранятся файлы логов и должен ли демон создавать пользовательские логи.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFiles" class="form-label">Конфигурационные Файлы</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files" class="form-control" rows="6">{{ old('config_files') }}</textarea>
                                <p class="text-muted small">Это должно быть JSON-представление конфигурационных файлов для изменения и какие части должны быть изменены.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStartup" class="form-label">Конфигурация Запуска</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup" class="form-control" rows="6">{{ old('config_startup') }}</textarea>
                                <p class="text-muted small">Это должно быть JSON-представление того, какие значения демон должен искать при запуске сервера для определения завершения.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success btn-sm pull-right">Создать</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    $(document).ready(function() {
        $('#pNestId').select2().change();
        $('#pConfigFrom').select2();
    });
    $('#pNestId').on('change', function (event) {
        $('#pConfigFrom').html('<option value="">Нет</option>').select2({
            data: $.map(_.get(Pterodactyl.nests, $(this).val() + '.eggs', []), function (item) {
                return {
                    id: item.id,
                    text: item.name + ' <' + item.author + '>',
                };
            }),
        });
    });
    $('textarea[data-action="handle-tabs"]').on('keydown', function(event) {
        if (event.keyCode === 9) {
            event.preventDefault();

            var curPos = $(this)[0].selectionStart;
            var prepend = $(this).val().substr(0, curPos);
            var append = $(this).val().substr(curPos);

            $(this).val(prepend + '    ' + append);
        }
    });
    </script>
@endsection
