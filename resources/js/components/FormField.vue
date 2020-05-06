<template>
    <default-field :field="field">
        <template slot="field">
            <div class="mb-6">
                <loading-card
                    :class="uploaderClasses"
                    :loading="loading">
                    <input
                        @mouseenter="hovered = true" @mouseleave="hovered = false"
                        @focus="focused = true" @blur="focused = false" tabindex="0"
                        type="file"
                        accept="image/*"
                        class="form-file-input z-10"
                        ref="fileField"
                        :name="field.name"
                        :id="field.name"
                        @change="fileChange" />

                    <div class="flex flex-col items-center justify-center w-full h-full z-20">
                        <template v-if="hasImage">
                            <div class="preview" :style="previewStyles" />

                            <div v-if="hasImage" class="w-full absolute pin-b p-4 flex items-center justify-between text-sm bg-90-half z-20">
                                <div class="text-50 ">
                                    {{ currentLabel }}
                                </div>

                                <div>
                                    <label :for="field.name">
                                        <icon-button
                                            v-if="shouldShowRemoveButton"
                                            @click="openFileInput"
                                            :dusk="field.attribute + '-edit-link'"
                                            type="edit"
                                            class="text-50 mr-3">
                                        </icon-button>
                                    </label>

                                    <icon-button
                                        v-if="shouldShowRemoveButton"
                                        :dusk="field.attribute + '-delete-link'"
                                        type="delete"
                                        class="text-50"
                                        @click="confirmRemoval">
                                    </icon-button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="text-xs font-bold uppercase tracking-wide mb-2">{{ __(uploadLabel) }}</div>

                            <div>
                                <upload-icon />
                            </div>
                        </template>
                    </div>
                </loading-card>
                <div
                    v-if="hasValueButImageMissing"
                    class="not-found mt-3 text-sm">
                    <span>
                        <a :href="field.thumbnailUrl" class="text-primary dim" target="_blank">
                            {{__('This field')}}
                        </a> {{__('has an image, but it could not be found. Try uploading a new one.')}}
                    </span>
                </div>
                <portal to="modals">
                    <transition name="fade">
                        <confirm-upload-removal-modal
                            v-if="removeModalOpen"
                            @confirm="removeFileOrPreview"
                            @close="closeRemoveModal"
                        />
                    </transition>
                </portal>
            </div>
            <p v-if="hasError" class="mt-4 text-danger">
                {{ firstError }}
            </p>
        </template>
    </default-field>
</template>

<script>
import IconButton from './IconButton'
import UploadIcon from './UploadIcon'
import { FormField, HandlesValidationErrors, Errors, Minimum } from 'laravel-nova'

export default {
    props: {
        uploadLabel: {
            type: String,
            default: 'Drag Image, Paste or Click',
        },
        resourceName: String,
        resourceId: String,
        relatedResourceName: String,
        relatedResourceId: String,
        viaRelationship: String,
    },

    mixins: [HandlesValidationErrors, FormField],

    components: { IconButton, UploadIcon },

    data: () => ({
        blob: '',
        thumbnail: '',
        focused: false,
        hovered: false,
        file: null,
        fileName: '',
        imagePreview: null,
        removeModalOpen: false,
        loading: true,
        missing: false,
        deleted: false,
        uploadErrors: new Errors(),
    }),

    mounted() {
        this.field.fill = formData => {
            formData.append(this.field.attribute, this.file, this.fileName)
        }

        if (this.field.thumbnailUrl) {
            this.fetchImage();
        } else {
            this.missing = true;
            this.loading = false;
        }
        window.addEventListener('paste', this.handlePaste);
    },

    destroyed() {
        window.removeEventListener('paste', this.handlePaste);
    },

    methods: {

        handlePaste(e) {
            if (!this.focused && !this.hovered) return
            this.file = e.clipboardData.files[0]
            this.fileName = 'image'
            this.missing = false
            this.deleted = false
            this.previewImage()
        },

        openFileInput() {
            this.$refs.fileField.click()
        },

        fetchImage() {
            Minimum(
                axios.get(`/images/${this.field.value}/get-preview-image`, {
                    responseType: 'blob',
                })
            )
                .then(({ headers, data }) => {

                    const blob = new Blob([data], { type: headers['content-type'] })
                    let newImage = new Image()
                    let fileName = this.field.value.match(/[^\\/]*$/)[0]
                    this.fileName = fileName
                    this.imagePreview = window.URL.createObjectURL(blob)
                    this.loading = false
                })
                .catch(error => {
                    this.missing = true
                    this.loading = false
                })
        },

        fileChange(event) {
            let path = event.target.value
            let fileName = path.match(/[^\\/]*$/)[0]

            if (!fileName) return

            this.fileName = fileName
            this.file = this.$refs.fileField.files[0]
            this.missing = false
            this.deleted = false
            this.previewImage()
        },

        previewImage() {
            if (this.file) {
                var reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                    this.loading = false;
                }
                this.loading = true;
                reader.readAsDataURL(this.file);
            }
        },

        confirmRemoval() {
            this.removeModalOpen = true
        },

        closeRemoveModal() {
            this.removeModalOpen = false
        },

        removeFileOrPreview() {
            this.imagePreview
                ? this.removePreview()
                : this.removeFile()
        },

        removePreview() {
            this.imagePreview = null
            this.fileName = null
            this.file = null
            this.deleted = true

            this.field.value
                ? this.removeFile()
                : this.closeRemoveModal()
        },

        async removeFile() {
            this.uploadErrors = new Errors()

            const {
                resourceName,
                resourceId,
                relatedResourceName,
                relatedResourceId,
                viaRelationship,
            } = this
            const attribute = this.field.attribute

            const uri = this.viaRelationship
                ? `/nova-api/${resourceName}/${resourceId}/${relatedResourceName}/${relatedResourceId}/field/${attribute}?viaRelationship=${viaRelationship}`
                : `/nova-api/${resourceName}/${resourceId}/field/${attribute}`

            try {
                await Nova.request().delete(uri)
                this.closeRemoveModal()
                this.deleted = true
                this.imagePreview = null
                this.$emit('file-deleted')
            } catch (error) {
                this.closeRemoveModal()

                if (error.response.status == 422) {
                    this.uploadErrors = new Errors(error.response.data.errors)
                }
            }
        },
    },

    computed: {
        uploaderClasses() {
            return [
                'uploader',
                'card',
                'overflow-hidden',
                'text-60',
                { 'border-2 border-dashed border-60': !this.imagePreview },
            ]
        },

        previewStyles() {
            return this.imagePreview
                ? { backgroundImage: `url(${this.imagePreview})` }
                : {};
        },

        hasError() {
            return (
                this.errors.has(this.fieldAttribute) || this.uploadErrors.has(this.fieldAttribute)
            )
        },

        firstError() {
            if (this.hasError) {
                return (
                    this.errors.first(this.fieldAttribute) ||
                    this.uploadErrors.first(this.fieldAttribute)
                )
            }
        },

        currentLabel() {
            return this.fileName || this.label
        },


        idAttr() {
            return this.labelFor
        },

        labelFor() {
            return `file-${this.field.attribute}`
        },


        hasImage() {
            return (
                Boolean(this.field.value || this.field.thumbnailUrl || this.imagePreview) &&
                !Boolean(this.deleted) &&
                !Boolean(this.missing)
            )
        },

        hasValueButImageMissing() {
            return (
                Boolean(this.field.value) &&
                !Boolean(this.deleted) &&
                !this.hasImage
            );
        },

        shouldShowLoader() {
            return !Boolean(this.deleted) && Boolean(this.field.thumbnailUrl)
        },

        shouldShowRemoveButton() {
            return Boolean(this.field.deletable)
        },
    },
}
</script>

<style lang="scss" scoped>
.uploader {
    position: relative;
    width: 100%;
    max-width: 600px;
    height: 300px;
}

.preview {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    width: 100%;
    height: 100%;
}

.form-file-input {
    width: 100%;
	height: 100%;
	opacity: 0;
	overflow: hidden;
	position: absolute;
    cursor: pointer;
}

</style>
