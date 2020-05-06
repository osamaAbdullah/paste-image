<template>
    <p>
        <img
            v-if="field.thumbnailUrl"
            :src="`/images/${field.value}/get-preview-image`"
            style="object-fit: cover;"
            class="rounded-full w-8 h-8" />

        <span v-else>&mdash;</span>
    </p>
</template>

<script>
export default {
    props: ['viaResource', 'viaResourceId', 'resourceName', 'field'],
}
</script>
