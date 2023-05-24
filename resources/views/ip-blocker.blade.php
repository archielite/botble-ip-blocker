@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        {!! Form::open(['route' => ['ip-blocker.updateIpBlocker']]) !!}
            <x-core-setting::section
                    :title="trans('plugins/ip-blocker::ip-blocker.ip_blocker_title')"
                    :description="trans('plugins/ip-blocker::ip-blocker.ip_blocker_description')"
            >

                {!! Form::tags('ip_addresses', json_decode(setting('ip-blocker:ip_addresses'), true)) !!}

                <button class="btn btn-info mt-3" type="submit">
                    {{ trans('plugins/ip-blocker::ip-blocker.ip_blocker_save') }}
                </button>
            </x-core-setting::section>
        {!! Form::close() !!}
    </div>
@endsection
