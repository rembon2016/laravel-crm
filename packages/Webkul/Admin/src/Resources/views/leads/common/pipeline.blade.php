@props([
    'pipelineId' => null,
    'stageId'    => null,
])

@php
    $pipelineRepository = app('Webkul\Lead\Repositories\PipelineRepository');

    $pipelines = $pipelineRepository->all();

    $pipelineId = old('lead_pipeline_id', $pipelineId);

    $stageId = old('lead_pipeline_stage_id', $stageId);

    if (! $pipelineId && $stageId) {
        $pipelineId = optional(app('Webkul\Lead\Repositories\StageRepository')->find($stageId))->lead_pipeline_id;
    }

    if (! $pipelineId) {
        $pipelineId = optional($pipelineRepository->getDefaultPipeline())->id;
    }

    $pipeline = $pipelines->firstWhere('id', (int) $pipelineId) ?? $pipelines->first();

    if (! $stageId) {
        $stageId = optional($pipeline?->stages->first())->id;
    }

    $attributeRepository = app('Webkul\Attribute\Repositories\AttributeRepository');

    $pipelineLabel = $attributeRepository->findOneWhere([
        'entity_type' => 'leads',
        'code'        => 'lead_pipeline_id',
    ])?->name ?: 'Pipeline';

    $stageLabel = $attributeRepository->findOneWhere([
        'entity_type' => 'leads',
        'code'        => 'lead_pipeline_stage_id',
    ])?->name ?: 'Stage';
@endphp

<div class="flex gap-4 max-sm:flex-wrap">
    <!-- Pipeline -->
    <div class="w-full">
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="required">
                {{ $pipelineLabel }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="select"
                name="lead_pipeline_id"
                id="lead_pipeline_id"
                rules="required"
                :value="$pipelineId"
                :label="$pipelineLabel"
            >
                @foreach ($pipelines as $pipelineOption)
                    <option
                        value="{{ $pipelineOption->id }}"
                        @selected($pipelineId == $pipelineOption->id)
                    >
                        {{ $pipelineOption->name }}
                    </option>
                @endforeach
            </x-admin::form.control-group.control>

            <x-admin::form.control-group.error control-name="lead_pipeline_id" />
        </x-admin::form.control-group>
    </div>

    <!-- Stage -->
    <div class="w-full">
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="required">
                {{ $stageLabel }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="select"
                name="lead_pipeline_stage_id"
                id="lead_pipeline_stage_id"
                rules="required"
                :value="$stageId"
                :label="$stageLabel"
            >
                @foreach ($pipelines as $pipelineOption)
                    @foreach ($pipelineOption->stages as $stageOption)
                        <option
                            value="{{ $stageOption->id }}"
                            data-pipeline-id="{{ $pipelineOption->id }}"
                            @selected($stageId == $stageOption->id)
                        >
                            {{ $stageOption->name }}
                        </option>
                    @endforeach
                @endforeach
            </x-admin::form.control-group.control>

            <x-admin::form.control-group.error control-name="lead_pipeline_stage_id" />
        </x-admin::form.control-group>
    </div>
</div>

@pushOnce('scripts')
    <script>
        /**
         * The Vue application is mounted on the window load event, so the
         * pipeline and stage selects only exist after that event.
         */
        const initPipelineStages = () => {
            setTimeout(() => {
                const pipeline = document.getElementById('lead_pipeline_id');

                const stage = document.getElementById('lead_pipeline_stage_id');

                if (! pipeline || ! stage) {
                    return;
                }

                /**
                 * Show only the stages that belong to the selected pipeline.
                 *
                 * @param {Boolean} notify - Whether to notify Vue about the changed value.
                 */
                const syncStages = (notify) => {
                    let firstMatchingOption = null;

                    Array.from(stage.options).forEach((option) => {
                        const matches = option.dataset.pipelineId === pipeline.value;

                        option.hidden = ! matches;

                        option.disabled = ! matches;

                        if (matches && ! firstMatchingOption) {
                            firstMatchingOption = option;
                        }
                    });

                    if (stage.selectedOptions[0]?.disabled) {
                        stage.value = firstMatchingOption ? firstMatchingOption.value : '';

                        if (notify) {
                            stage.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                };

                pipeline.addEventListener('change', () => syncStages(true));

                syncStages(false);
            }, 0);
        };

        if (document.readyState === 'complete') {
            initPipelineStages();
        } else {
            window.addEventListener('load', initPipelineStages);
        }
    </script>
@endPushOnce
