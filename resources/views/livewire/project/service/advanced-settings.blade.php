<div class="grid gap-4 sm:grid-cols-2">
    @if ($resourceType === 'application')
        @if (str($serviceApplication->image)->contains('pocketbase'))
            <x-forms.listbox id="isGzipEnabled" :label="__('common.gzip_compression')"
                :helper="__('common.gzip_helper')"
                :disabled="true" :options="[
                    ['value' => true, 'label' => __('common.enabled')],
                    ['value' => false, 'label' => __('common.disabled')],
                ]" />
        @else
            <x-forms.listbox id="isGzipEnabled" :label="__('common.gzip_compression')"
                :options="[
                    ['value' => true, 'label' => __('common.enabled')],
                    ['value' => false, 'label' => __('common.disabled')],
                ]" />
        @endif
        <x-forms.listbox id="isStripprefixEnabled" :label="__('common.path_prefixes')"
            :options="[
                ['value' => true, 'label' => __('common.strip_prefixes')],
                ['value' => false, 'label' => __('common.keep_prefixes')],
            ]" />
        <x-forms.listbox id="excludeFromStatus" :label="__('common.service_status')"
            :options="[
                ['value' => false, 'label' => __('common.include_in_status')],
                ['value' => true, 'label' => __('common.exclude_from_status')],
            ]" />
        <x-forms.listbox id="isLogDrainEnabled" :label="__('common.log_drain')"
            :options="[
                ['value' => true, 'label' => __('common.send_logs_to_drain')],
                ['value' => false, 'label' => __('common.do_not_drain_logs')],
            ]" />
        <x-forms.input type="number" min="0" id="maxRestartCount" :label="__('common.max_restart_count')"
            :helper="__('common.max_restart_count_helper_service')"
            canGate="update" :canResource="$serviceApplication" />
    @else
        <x-forms.listbox id="excludeFromStatus" :label="__('common.service_status')"
            :options="[
                ['value' => false, 'label' => __('common.include_in_status')],
                ['value' => true, 'label' => __('common.exclude_from_status')],
            ]" />
        <x-forms.listbox id="isLogDrainEnabled" :label="__('common.log_drain')"
            :options="[
                ['value' => true, 'label' => __('common.send_logs_to_drain')],
                ['value' => false, 'label' => __('common.do_not_drain_logs')],
            ]" />
    @endif
</div>
