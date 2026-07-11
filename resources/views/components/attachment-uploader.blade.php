@props([
    'name' => 'anexos[]',
    'maxFiles' => 5,
    'maxSizeMb' => 5,
    'accept' => '.jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp4,.kmz,.kml,.zip',
    'title' => 'Clique ou arraste arquivos para anexar',
    'help' => null,
])
@php
    $inputId = 'attachments-' . \Illuminate\Support\Str::uuid();
    $extensions = str_replace('.', '', $accept);
@endphp
<div {{ $attributes->merge(['class' => 'btx-uploader']) }} data-btx-uploader data-max-files="{{ $maxFiles }}" data-max-size-mb="{{ $maxSizeMb }}" data-extensions="{{ $extensions }}">
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
    <div class="btx-uploader__errors" role="alert" aria-live="assertive" hidden></div>
    <ul class="btx-uploader__files" aria-live="polite" aria-label="Arquivos selecionados"></ul>
</div>
