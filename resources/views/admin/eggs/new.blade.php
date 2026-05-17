@extends('layouts.admin')

@section('title')
    Nests &rarr; Новый Egg
@endsection

@section('content-header')
    <h1>Новый Egg<small>Создать новый Egg для назначения серверам.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li><a href="{{ route('admin.nests') }}">Гнезда</a></li>
        <li class="active">Новый Egg</li>
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
                                <label for="pNestId" class="form-label">Связанный Nest</label>
                                <div>
                                    <select name="nest_id" id="pNestId">
                                        @foreach($nests as $nest)
                                            <option value="{{ $nest->id }}" {{ old('nest_id') != $nest->id ?: 'selected' }}>{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small">Думайте о Nest как о категории. В один Nest можно поместить несколько Eggs, но лучше группировать только связанные между собой Eggs.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pName" class="form-label">Название</label>
                                <input type="text" id="pName" name="name" value="{{ old('name') }}" class="form-control" />
                                <p class="text-muted small">Простое и понятное человеку имя, используемое как идентификатор этого Egg. Именно его пользователи будут видеть как тип игрового сервера.</p>
                            </div>
                            <div class="form-group">
                                <label for="pDescription" class="form-label">Описание</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="8">{{ old('description') }}</textarea>
                                <p class="text-muted small">Описание этого Egg.</p>
                            </div>
                            <div class="form-group">
                                <div class="checkbox checkbox-primary no-margin-bottom">
                                    <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1" {{ \Pterodactyl\Helpers\Utilities::checked('force_outgoing_ip', 0) }} />
                                    <label for="pForceOutgoingIp" class="strong">Принудительный исходящий IP</label>
                                    <p class="text-muted small">
                                        Принудительно заставляет весь исходящий сетевой трафик использовать в качестве Source IP NAT-адрес основного allocation IP сервера.
                                        Требуется для корректной работы некоторых игр, если у ноды несколько публичных IP-адресов.
                                        <br>
                                        <strong>
                                            Включение этой опции отключит внутреннюю сеть для любых серверов, использующих этот egg,
                                            из-за чего они не смогут обращаться к другим серверам на той же ноде по внутренней сети.
                                        </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pDockerImage" class="control-label">Docker-образы</label>
                                <textarea id="pDockerImages" name="docker_images" rows="4" placeholder="ghcr.io/pterodactyl/yolks" class="form-control">{{ old('docker_images') }}</textarea>
                                <p class="text-muted small">Docker-образы, доступные серверам, использующим этот egg. Указывайте по одному на строку. Если указано несколько значений, пользователь сможет выбрать один из списка.</p>
                            </div>
                            <div class="form-group">
                                <label for="pStartup" class="control-label">Команда запуска</label>
                                <textarea id="pStartup" name="startup" class="form-control" rows="10">{{ old('startup') }}</textarea>
                                <p class="text-muted small">Команда запуска по умолчанию, которая будет использоваться для новых серверов, созданных с этим Egg. При необходимости её можно изменить для каждого сервера отдельно.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigFeatures" class="control-label">Возможности</label>
                                <div>
                                    <select class="form-control" name="features[]" id="pConfigFeatures" multiple>
                                    </select>
                                    <p class="text-muted small">Дополнительные возможности, относящиеся к egg. Полезно для настройки дополнительных модификаций панели.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Управление процессом</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="alert alert-warning">
                                <p>Все поля обязательны, если только вы не выберете отдельную опцию в выпадающем списке «Копировать настройки из». В этом случае поля можно оставить пустыми, чтобы использовать значения из выбранной опции.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFrom" class="form-label">Копировать настройки из</label>
                                <select name="config_from" id="pConfigFrom" class="form-control">
                                    <option value="">Нет</option>
                                </select>
                                <p class="text-muted small">Если вы хотите использовать настройки другого Egg по умолчанию, выберите его в выпадающем списке выше.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStop" class="form-label">Команда остановки</label>
                                <input type="text" id="pConfigStop" name="config_stop" class="form-control" value="{{ old('config_stop') }}" />
                                <p class="text-muted small">Команда, которая должна отправляться процессам сервера для их корректной остановки. Если нужно отправить <code>SIGINT</code>, укажите здесь <code>^C</code>.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigLogs" class="form-label">Конфигурация логов</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs" class="form-control" rows="6">{{ old('config_logs') }}</textarea>
                                <p class="text-muted small">Здесь должно быть JSON-представление того, где хранятся файлы логов и должен ли daemon создавать пользовательские логи.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFiles" class="form-label">Файлы конфигурации</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files" class="form-control" rows="6">{{ old('config_files') }}</textarea>
                                <p class="text-muted small">Здесь должно быть JSON-представление конфигурационных файлов, которые нужно изменять, и того, какие части должны быть изменены.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStartup" class="form-label">Конфигурация запуска</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup" class="form-control" rows="6">{{ old('config_startup') }}</textarea>
                                <p class="text-muted small">Здесь должно быть JSON-представление значений, которые daemon должен искать при запуске сервера для определения завершения запуска.</p>
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
    $('#pConfigFeatures').select2({
        tags: true,
        selectOnClose: false,
        tokenSeparators: [',', ' '],
    });
    </script>
@endsection