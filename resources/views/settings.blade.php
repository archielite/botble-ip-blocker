@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div>
        {!! Form::open(['route' => ['ip-blocker.settings.update']]) !!}
        <x-core-setting::section
                :title="trans('plugins/ip-blocker::ip-blocker.ip_blocker_title')"
                :description="trans('plugins/ip-blocker::ip-blocker.ip_blocker_description')"
        >

            {!! Form::text('ip_addresses', $ips, ['class' => 'tags', 'placeholder' => trans('plugins/ip-blocker::ip-blocker.please_enter_ip_address')]) !!}

            <button class="btn btn-info mt-3" type="submit">
                {{ trans('plugins/ip-blocker::ip-blocker.save_settings') }}
            </button>
        </x-core-setting::section>
        {!! Form::close() !!}

        <x-core-setting::section
                :title="trans('plugins/ip-blocker::ip-blocker.history')"
                :description="trans('plugins/ip-blocker::ip-blocker.history_description')"
        >
            <div class="table-wrapper" style="padding-top: 45px">
                {!! $historyTable->renderTable() !!}
            </div>
        </x-core-setting::section>
    </div>
@endsection
