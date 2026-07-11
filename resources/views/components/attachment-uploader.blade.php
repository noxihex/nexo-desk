@props([
    'name' => 'anexos[]',
    'maxFiles' => 5,
    'maxSizeMb' => 10,
    'accept' => '.jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp4,.kmz,.kml,.zip',
    'title' => 'Clique ou arraste arquivos para anexar',
    'help' => null,
    'collapsible' => false,
])
@php
    $inputId = 'attachments-' . \Illuminate\Support\Str::uuid();
    $panelId = $inputId . '-panel';
    $fieldName = \Illuminate\Support\Str::before($name, '[');
    $hasAttachmentErrors = $errors->has($fieldName) || $errors->has($fieldName . '.*');
    $isOpen = !$collapsible || $hasAttachmentErrors;
    $extensions = str_replace('.', '', $accept);
@endphp
<div {{ $attributes->merge(['class' => 'btx-uploader']) }} data-btx-uploader data-max-files="{{ $maxFiles }}" data-max-size-mb="{{ $maxSizeMb }}" data-extensions="{{ $extensions }}">
    @if($collapsible)
        <button class="btn btn-outline-secondary btn-sm btx-uploader__toggle" type="button" aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="{{ $panelId }}">
            <i class="fas fa-paperclip" aria-hidden="true"></i>
            <span>Anexar arquivos</span>
            <span class="btx-uploader__toggle-count">Nenhum arquivo selecionado</span>
        </button>
    @endif
    <div id="{{ $panelId }}" class="btx-uploader__panel" @if(!$isOpen) hidden @endif>
    <label id="{{ $inputId }}-label" class="sr-only" for="{{ $inputId }}">Selecionar anexos</label>
    <div class="btx-uploader__dropzone" role="button" tabindex="0" aria-labelledby="{{ $inputId }}-title" aria-describedby="{{ $inputId }}-help">
        <input id="{{ $inputId }}" class="btx-uploader__input" type="file" name="{{ $name }}" accept="{{ $accept }}" multiple>
        <span>
            <i class="fas fa-cloud-upload-alt btx-uploader__icon" aria-hidden="true"></i>
            <span id="{{ $inputId }}-title" class="btx-uploader__title">{{ $title }}</span>
            <span id="{{ $inputId }}-help" class="btx-uploader__help">{{ $help ?: "Até {$maxFiles} arquivos, com no máximo {$maxSizeMb} MB cada" }}</span>
        </span>
    </div>
    <div class="btx-uploader__summary"><span>JPG, PNG, PDF, Office, TXT, MP4, KML/KMZ ou ZIP</span><span class="btx-uploader__counter">0 de {{ $maxFiles }} arquivos</span></div>
    <div class="btx-uploader__errors" role="alert" aria-live="assertive" @if(!$hasAttachmentErrors) hidden @endif>
        @foreach($errors->get($fieldName) as $message)
            <div>{{ $message }}</div>
        @endforeach
        @foreach($errors->get($fieldName . '.*') as $messages)
            @foreach($messages as $message)
                <div>{{ $message }}</div>
            @endforeach
        @endforeach
    </div>
    <ul class="btx-uploader__files" aria-live="polite" aria-label="Arquivos selecionados"></ul>
    </div>
</div>
