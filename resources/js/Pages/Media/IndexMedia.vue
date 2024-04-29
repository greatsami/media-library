<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from "@/Components/Pagination.vue";
import SelectAction from "@/Components/SelectAction.vue";
</script>
<script>
import {pickBy} from "lodash/object.js";

export default {
    props: {
        media: Object,
        fileTypes: Array,
        months: Array,
        queryParams: Object,
    },
    data() {
        return {
            query: {
                fileType: this.queryParams.fileType,
                month: this.queryParams.month,
                term: this.queryParams.term,
            },
            actions: [
                {id: null, label: "Bulk actions"},
                {id: "delete", label: "Delete"},
            ]
        }
    },
    computed: {
        allFileTypes() {
            return [
                {
                    value: null,
                    label: "Any type"
                },
                ...this.fileTypes
            ]
        },
        allMonths() {
            return [
                {
                    value: null,
                    label: "Any date"
                },
                ...this.months
            ]
        }
    },
    methods: {
        filter() {
            this.$inertia.get(route('media.index'), pickBy(this.query), {
                preserveState: true
            });
        },
        executeAction(actionId) {
            const mediaIds = this.media.data.filter(media => media.selected)
                .map(media => media.id);
            if (!mediaIds.length) return;

            switch (actionId) {
                case 'delete':
                    if (confirm(`Are you sure?`)) {
                        this.$inertia.delete(route('media.destroy_multiple'), {
                            preserveState: false,
                            data: {
                                mediaIds: mediaIds,
                            },
                        });
                    }
                    break;
            }
        },
        toggleSelectAll(e) {
            this.media.data.forEach(media => media.selected = e.target.checked);
        },
        deleteMedia(mediaId) {
            if (confirm(`Are you sure?`)) {
                this.$inertia.delete(route('media.destroy', mediaId), {
                    preserveState: false
                });
            }
        }
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Media Library</h2>
                <Link :href="route('media.create')" class="inline-flex items-center px-3 h-11 font-medium text-gray-700 bg-white rounded border border-gray-300 shadow-sm lg:h-7 lg:text-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Add new
                </Link>
            </div>
        </template>

        <div class="p-8 mx-auto max-w-7xl">
            <section class="flex flex-col p-4 mb-4 space-y-4 bg-white shadow sm:rounded lg:flex-row lg:items-center lg:justify-between lg:space-y-0 lg:p-2">
                <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-2">
                    <select v-model="query.fileType" aria-label="Media type" id="type" class="pr-10 pl-3 w-full h-11 rounded border-gray-300 shadow-sm lg:h-9 lg:text-sm sm:w-44 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option v-for="type in allFileTypes" :value="type.value">{{ type.label }}</option>
                    </select>

                    <select v-model="query.month" aria-label="Media date" id="date" class="pr-10 pl-3 w-full h-11 rounded border-gray-300 shadow-sm lg:h-9 lg:text-sm sm:w-44 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option v-for="month in allMonths" :value="month.value">{{ month.label }}</option>
                    </select>

                    <button @click="filter()" type="button" class="inline-flex items-center px-4 h-11 font-medium text-gray-700 bg-white rounded border border-gray-300 shadow-sm lg:h-9 lg:text-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Filter
                    </button>
                </div>

                <div class="flex flex-col">
                    <label for="search" class="text-sm font-medium text-gray-700 sr-only">Search</label>
                    <input v-model="query.term" @keydown.enter="filter()" type="search" id="search" class="w-full h-11 rounded border-gray-300 shadow-sm lg:h-9 lg:text-sm lg:w-64 focus:ring-blue-500 focus:border-blue-500" placeholder="Search for..."/>
                </div>
            </section>

            <section class="flex flex-col mb-4 lg:flex-row lg:justify-between">
                <SelectAction :actions="actions" @execute="executeAction" />

                <Pagination :pagination="media.meta" />
            </section>

            <section class="mb-4">
                <table class="min-w-full bg-white shadow table-fixed sm:rounded">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-2 w-10 text-center">
                            <input type="checkbox" @change="toggleSelectAll" class="w-6 h-6 text-blue-600 rounded border-gray-300 lg:w-4 lg:h-4 focus:ring-blue-500">
                        </th>
                        <th class="text-left">
                            <Link
                                href="#"
                                class="flex relative z-10 items-center p-2 space-x-2 font-normal text-blue-600 group lg:text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 focus:ring-blue-500">
                                File
                            </Link>
                        </th>
                        <th class="hidden w-48 text-left lg:table-cell">
                            <span class="inline-block p-2 font-normal text-blue-600 lg:text-sm">Author</span>
                        </th>
                        <th class="hidden w-28 text-left lg:table-cell">
                            <Link
                                href="#"
                                class="flex relative z-10 items-center p-2 space-x-2 font-normal text-blue-600 group lg:text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 focus:ring-blue-500">
                                Date
                            </Link>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                    <tr class="align-top group" v-for="(item, index) in media.data" :key="item.id">
                        <td class="p-2 w-10 text-center">
                            <input type="checkbox" v-model="item.selected" class="w-6 h-6 text-blue-600 rounded border-gray-300 lg:w-4 lg:h-4 focus:ring-blue-500">
                        </td>
                        <td class="p-2 text-left">
                            <div class="flex space-x-4">
                                <div class="overflow-hidden flex-shrink-0 w-12 h-12 bg-gray-100 rounded lg:w-16 lg:h-16">
                                    <img :src="item.preview_url" class="object-cover">
                                </div>
                                <div>
                                    <Link href="#" class="text-sm font-semibold text-blue-600 break-all rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        {{ item.name }}
                                    </Link>
                                    <p class="text-xs text-gray-500 break-all">{{ item.file_name }}</p>

                                    <div class="flex items-center mt-2 space-x-2 lg:invisible group-hover:visible">
                                        <Link href="#" class="text-xs text-blue-600 rounded hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            Edit
                                        </Link>
                                        <span class="text-xs text-gray-300">|</span>
                                        <button @click="deleteMedia(item.id)" class="text-xs text-red-600 rounded hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            Delete
                                        </button>
                                        <span class="text-xs text-gray-300">|</span>
                                        <a :href="item.url" class="text-xs text-blue-600 rounded hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden p-2 text-left lg:table-cell">
                            <a href="#" class="text-blue-600 rounded lg:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                {{ item.user.name }}
                            </a>
                        </td>
                        <td class="hidden p-2 text-left lg:table-cell">
                            <span class="text-gray-600 lg:text-sm">{{  item.created_at  }}</span>
                        </td>
                    </tr>

                    <tr class="align-top" v-if="!media.data.length">
                        <td colspan="4" class="p-2 text-sm text-gray-700">
                            No media files found.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </section>

            <section class="flex flex-col mb-4 lg:flex-row lg:justify-between">
                <SelectAction :actions="actions" @execute="executeAction" />

                <Pagination :pagination="media.meta" />
            </section>


        </div>
    </AuthenticatedLayout>
</template>
