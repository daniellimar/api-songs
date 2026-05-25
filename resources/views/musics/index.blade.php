@extends('layouts.app')

@section('title', 'Upload de Músicas')

@section('content')

    <div class="container py-5">

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <h1 class="mb-4">
                    Upload de Músicas
                </h1>

                <div class="mb-3">

                    <input
                        type="file"
                        id="file"
                        accept="audio/*"
                        class="form-control"
                    >

                </div>

                <button
                    id="uploadBtn"
                    class="btn btn-dark"
                >
                    Enviar Música
                </button>

                <div
                    class="progress mt-4"
                    style="height: 22px;"
                >

                    <div
                        id="progressBar"
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar"
                        style="width: 0%"
                    ></div>

                </div>

                <p
                    id="progressText"
                    class="mt-2 mb-0"
                >
                    0%
                </p>

            </div>

        </div>

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <h2 class="mb-4">
                    Músicas
                </h2>

                <div id="musics"></div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        const uploadBtn =
            document.getElementById('uploadBtn');

        const progressBar =
            document.getElementById('progressBar');

        const progressText =
            document.getElementById('progressText');

        const musicsContainer =
            document.getElementById('musics');

        async function loadMusics() {

            const response =
                await fetch('/api/musics');

            const data =
                await response.json();

            musicsContainer.innerHTML = '';

            data.data.forEach((music) => {

                musicsContainer.innerHTML += `

                    <div class="border-bottom pb-4 mb-4">

                        <h5 class="mb-1">
                            ${music.title}
                        </h5>

                        <p class="text-muted mb-2">
                            ${music.artist ?? 'Artista desconhecido'}
                        </p>

                        <p class="small text-secondary">
                            ${music.duration ?? '--'} segundos
                        </p>

                        <audio
                            controls
                            class="w-100 mb-3"
                        >
                            <source
                                src="/api/musics/${music.id}/stream"
                                type="audio/mpeg"
                            >
                        </audio>

                        <button
                            class="btn btn-danger btn-sm"
                            onclick="removeMusic(${music.id})"
                        >
                            Remover
                        </button>

                    </div>
                `;
            });
        }

        async function removeMusic(id) {

            const confirmed = confirm(
                'Deseja remover esta música?'
            );

            if (!confirmed) {
                return;
            }

            await fetch(`/api/musics/${id}`, {
                method: 'DELETE'
            });

            await loadMusics();
        }

        uploadBtn.addEventListener(
            'click',
            async () => {

                const fileInput =
                    document.getElementById('file');

                const file =
                    fileInput.files[0];

                if (!file) {

                    alert('Selecione uma música');

                    return;
                }

                uploadBtn.disabled = true;

                const chunkSize =
                    5 * 1024 * 1024;

                const totalChunks =
                    Math.ceil(file.size / chunkSize);

                const uploadId =
                    crypto.randomUUID();

                for (
                    let chunkIndex = 0;
                    chunkIndex < totalChunks;
                    chunkIndex++
                ) {

                    const start =
                        chunkIndex * chunkSize;

                    const end = Math.min(
                        start + chunkSize,
                        file.size
                    );

                    const chunk =
                        file.slice(start, end);

                    const formData =
                        new FormData();

                    formData.append(
                        'chunk',
                        chunk,
                        file.name
                    );

                    formData.append(
                        'chunk_index',
                        chunkIndex
                    );

                    formData.append(
                        'total_chunks',
                        totalChunks
                    );

                    formData.append(
                        'upload_id',
                        uploadId
                    );

                    formData.append(
                        'file_name',
                        file.name
                    );

                    formData.append(
                        'file_size',
                        file.size
                    );

                    const response = await fetch(
                        '/api/musics/upload',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );

                    if (!response.ok) {

                        const error =
                            await response.text();

                        console.error(error);

                        alert('Erro no upload');

                        uploadBtn.disabled = false;

                        return;
                    }

                    const progress = Math.round(
                        ((chunkIndex + 1) / totalChunks) * 100
                    );

                    progressBar.style.width =
                        progress + '%';

                    progressText.innerText =
                        progress + '%';
                }

                alert('Upload concluído');

                uploadBtn.disabled = false;

                fileInput.value = '';

                progressBar.style.width = '0%';

                progressText.innerText = '0%';

                await loadMusics();
            }
        );

        loadMusics();

    </script>

@endpush
