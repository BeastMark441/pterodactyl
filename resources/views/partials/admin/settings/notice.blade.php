@section('settings::notice')
    @if(config('pterodactyl.load_environment_only', false))
        <div class="row">
            <div class="col-xs-12">
                <div class="alert alert-danger">
                    Ваша панель настроен на чтение настроек только из окружения. Вам необходимо установить <code>APP_ENVIRONMENT_ONLY=false</code> в файле окружения, чтобы загрузить настройки динамически.
                </div>
            </div>
        </div>
    @endif
@endsection
