@extends('layouts.admin')

@section('title')
    Ноды &rarr; Новая
@endsection

@section('content-header')
    <h1>Новая нода<small>Создайте новую локальную или удалённую ноду для размещения серверов.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li><a href="{{ route('admin.nodes') }}">Ноды</a></li>
        <li class="active">Новая</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.nodes.new') }}" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Основные данные</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pName" class="form-label">Название</label>
                        <input type="text" name="name" id="pName" class="form-control" value="{{ old('name') }}"/>
                        <p class="text-muted small">Допустимые символы: <code>a-zA-Z0-9_.-</code> и <code>[Пробел]</code> (минимум 1, максимум 100 символов).</p>
                    </div>
                    <div class="form-group">
                        <label for="pDescription" class="form-label">Описание</label>
                        <textarea name="description" id="pDescription" rows="4" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="pLocationId" class="form-label">Локация</label>
                        <select name="location_id" id="pLocationId">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $location->id != old('location_id') ?: 'selected' }}>{{ $location->short }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Видимость ноды</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pPublicTrue" value="1" name="public" checked>
                                <label for="pPublicTrue">Публичная</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pPublicFalse" value="0" name="public">
                                <label for="pPublicFalse">Приватная</label>
                            </div>
                        </div>
                        <p class="text-muted small">Если сделать ноду <code>private</code>, автоматическое размещение серверов на ней будет недоступно.</p>
                    </div>
                    <div class="form-group">
                        <label for="pFQDN" class="form-label">FQDN</label>
                        <input type="text" name="fqdn" id="pFQDN" class="form-control" value="{{ old('fqdn') }}"/>
                        <p class="text-muted small">Введите доменное имя (например, <code>node.example.com</code>), которое будет использоваться для подключения к daemon. IP-адрес можно использовать <em>только</em>, если для этой ноды не используется SSL.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Соединение по SSL</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pSSLTrue" value="https" name="scheme" checked>
                                <label for="pSSLTrue">Использовать SSL-соединение</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pSSLFalse" value="http" name="scheme" @if(request()->isSecure()) disabled @endif>
                                <label for="pSSLFalse">Использовать HTTP-соединение</label>
                            </div>
                        </div>
                        @if(request()->isSecure())
                            <p class="text-danger small">Ваша панель сейчас настроена на безопасное соединение. Чтобы браузеры могли подключаться к ноде, она <strong>обязательно</strong> должна использовать SSL.</p>
                        @else
                            <p class="text-muted small">В большинстве случаев следует использовать SSL-соединение. Если используется IP-адрес или SSL не нужен, выберите HTTP-соединение.</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">За прокси</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" checked>
                                <label for="pProxyFalse">Не за прокси</label>
                            </div>
                            <div class="radio radio-info radio-inline">
                                <input type="radio" id="pProxyTrue" value="1" name="behind_proxy">
                                <label for="pProxyTrue">За прокси</label>
                            </div>
                        </div>
                        <p class="text-muted small">Если daemon работает за прокси, например Cloudflare, выберите это, чтобы daemon не пытался искать сертификаты при запуске.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Конфигурация</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDaemonBase" class="form-label">Каталог файлов серверов daemon</label>
                            <input type="text" name="daemonBase" id="pDaemonBase" class="form-control" value="/var/lib/pterodactyl/volumes" />
                            <p class="text-muted small">Укажите каталог, где должны храниться файлы серверов. <strong>Если вы используете OVH, проверьте схему разделов. Возможно, потребуется использовать <code>/home/daemon-data</code>, чтобы хватило места.</strong></p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pMemory" class="form-label">Общий объём памяти</label>
                            <div class="input-group">
                                <input type="text" name="memory" data-multiplicator="true" class="form-control" id="pMemory" value="{{ old('memory') }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pMemoryOverallocate" class="form-label">Перераспределение памяти</label>
                            <div class="input-group">
                                <input type="text" name="memory_overallocate" class="form-control" id="pMemoryOverallocate" value="{{ old('memory_overallocate') }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">Укажите общий объём памяти, доступный для новых серверов. Если хотите разрешить перераспределение памяти, укажите допустимый процент. Чтобы отключить проверку перераспределения, введите <code>-1</code>. Значение <code>0</code> запретит создание новых серверов, если это превысит лимит ноды.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDisk" class="form-label">Общий объём диска</label>
                            <div class="input-group">
                                <input type="text" name="disk" data-multiplicator="true" class="form-control" id="pDisk" value="{{ old('disk') }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pDiskOverallocate" class="form-label">Перераспределение диска</label>
                            <div class="input-group">
                                <input type="text" name="disk_overallocate" class="form-control" id="pDiskOverallocate" value="{{ old('disk_overallocate') }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">Укажите общий объём дискового пространства, доступный для новых серверов. Если хотите разрешить перераспределение диска, укажите допустимый процент. Чтобы отключить проверку перераспределения, введите <code>-1</code>. Значение <code>0</code> запретит создание новых серверов, если это превысит лимит ноды.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDaemonListen" class="form-label">Порт daemon</label>
                            <input type="text" name="daemonListen" class="form-control" id="pDaemonListen" value="8080" />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pDaemonSFTP" class="form-label">SFTP-порт daemon</label>
                            <input type="text" name="daemonSFTP" class="form-control" id="pDaemonSFTP" value="2022" />
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">Daemon использует собственный контейнер управления SFTP и не использует процесс SSHd основного физического сервера. <strong>Не используйте тот же порт, что назначен SSH на физическом сервере.</strong> Если daemon будет работать за CloudFlare&reg;, рекомендуется установить порт daemon на <code>8443</code>, чтобы websocket-проксирование работало через SSL.</p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success pull-right">Создать ноду</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#pLocationId').select2();
    </script>
@endsection