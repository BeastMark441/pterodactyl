@extends('layouts.admin')

@section('title')
    Администрирование
@endsection

@section('content-header')
    <h1>Обзор панели <small>Быстрый взгляд на вашу систему.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Админ</a></li>
        <li class="active">Главная</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box
            @if($version->isLatestPanel() && $version->isLatestTranslation())
                box-success
            @else
                box-danger
            @endif
        ">
            <div class="box-header with-border">
                <h3 class="box-title">Информация о системе</h3>
            </div>
            <div class="box-body">
                @if ($version->isLatestPanel())
                    <p>
                        Вы используете Pterodactyl Panel версии <code>{{ config('app.version') }}</code>. Ваша панель обновлена!
                    </p>
                @else
                    <p>
                        Ваша панель <strong>не обновлена!</strong>
                        Последняя версия
                        <a href="https://github.com/Pterodactyl/Panel/releases/v{{ $version->getPanel() }}" target="_blank" rel="noopener noreferrer">
                            <code>{{ $version->getPanel() }}</code>
                        </a>,
                        а вы используете версию <code>{{ config('app.version') }}</code>.
                    </p>
                @endif

                <hr>

                @if ($version->isLatestTranslation())
                    <p>
                        Вы используете русский перевод версии <code>{{ config('app.translation_version') }}</code> от
                        <a href="https://github.com/BeastMark441/pterodactyl" target="_blank" rel="noopener noreferrer">BeastMark441</a>.
                        Ваш перевод актуален!
                    </p>
                @else
                    <p>
                        Вы используете русский перевод версии <code>{{ config('app.translation_version') }}</code> от
                        <a href="https://github.com/BeastMark441/pterodactyl" target="_blank" rel="noopener noreferrer">BeastMark441</a>.
                        Доступна новая версия:
                        <a href="https://github.com/BeastMark441/pterodactyl/releases/tag/{{ $version->getTranslation() }}" target="_blank" rel="noopener noreferrer">
                            <code>{{ $version->getTranslation() }}</code>
                        </a>.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDiscord() }}" target="_blank" rel="noopener noreferrer">
            <button class="btn btn-warning" style="width:100%;">
                <i class="fa fa-fw fa-support"></i> Получить помощь <small>(через Discord)</small>
            </button>
        </a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://pterodactyl.io" target="_blank" rel="noopener noreferrer">
            <button class="btn btn-primary" style="width:100%;">
                <i class="fa fa-fw fa-link"></i> Документация
            </button>
        </a>
    </div>
    <div class="clearfix visible-xs-block">&nbsp;</div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://github.com/pterodactyl/panel" target="_blank" rel="noopener noreferrer">
            <button class="btn btn-primary" style="width:100%;">
                <i class="fa fa-fw fa-github"></i> GitHub
            </button>
        </a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDonations() }}" target="_blank" rel="noopener noreferrer">
            <button class="btn btn-success" style="width:100%;">
                <i class="fa fa-fw fa-money"></i> Поддержать проект
            </button>
        </a>
    </div>
</div>
@endsection
