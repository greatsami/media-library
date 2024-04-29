<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>
<script>
export default {
    data() {
        return {
            dragging: false,
            media: [],
            error: null,
            uploaded: false,
            preview_url: null,
            id: null,
        }
    },
    methods: {
        onDroppedFiles($event) {
            this.dragging = false;
            let files = [...$event.dataTransfer.items]
                .filter(item => item.kind === 'file')
                .map(item => item.getAsFile());

            this.uploadFiles(files);
        },

        onSelectedFiles($event) {
            let files = [...$event.target.files];

            this.uploadFiles(files);

            this.$refs.files.value = null;
        },

        uploadFiles(files) {
            files.forEach(file => {
                this.media.unshift({
                    file: file,
                    progress: 0
                })
            })

            this.media.filter(media => !media.uploaded)
                .forEach(media => {
                    let form = new FormData;
                    form.append('file', media.file);

                    axios.post(this.route('media.store'), form, {
                        onUploadProgress: (event) => {
                            media.progress = Math.round(event.loaded * 100 / event.total);
                        }
                    })
                        .then(({data}) => {
                            media.uploaded = true;
                            media.id = data.id;
                            media.preview_url = data.preview_url;
                        })
                        .catch(error => {
                            media.error = `Upload fail. Please try again later.`;

                            if (error?.response.status === 422) {
                                media.error = error.response.data.errors.file[0];
                            }
                        })
                })
        }
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add new media</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    @drop.prevent="onDroppedFiles"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    :class="[dragging ? 'border-indigo-500' : 'border-gray-400', 'flex flex-col px-6 py-12 items-center border-2 border-dashed rounded-md']"
                >
                    <svg class="w-12 h-12 text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>

                    <p class="text-xl text-gray-700">Drop files to upload</p>

                    <p class="mb-2 text-gray-700">or</p>

                    <label class="bg-white px-4 h-9 inline-flex items-center rounded border-gray-300 shadow-sm text-sm font-medium text-gray-700 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        Select files
                        <input @input="onSelectedFiles" type="file" ref="files" name="files" multiple class="sr-only">
                    </label>

                    <p class="text-xs text-gray-600 mt-4">Maximum upload file size: 512MB.</p>
                </div>

                <ul class="my-6 bg-white rounded divide-y divide-gray-200 shadow">
                    <li v-for="(item, index) in media" :key="index" class="p-3 flex items-center space-x-2">
                        <div class="w-9 h-9 bg-gray-300 flex-shrink-0">
                            <img :src="item.preview_url" class="h-full w-full rounded" :alt="item.file.name">
                        </div>


                        <div class="text-sm text-gray-700 flex-1 truncate">{{ item.file.name }}</div>

                        <div v-if="!item.uploaded && !item.error" class="w-40 bg-gray-200 rounded-full h-5 shadow-inner overflow-hidden relative flex items-center justify-center">
                            <div class="inline-block h-full bg-indigo-600 absolute top-0 left-0" :style="`width: ${item.progress}%`"></div>
                            <div class="relative z-10 text-xs font-semibold text-center text-white drop-shadow text-shadow">
                                {{ item.progress }}%
                            </div>
                        </div>

                        <div v-if="item.error" class="text-sm text-red-600">{{ item.error }}</div>
                        <Link href="#" v-if="item.uploaded" class="text-sm text-indigo-600 underline">Edit</Link>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
