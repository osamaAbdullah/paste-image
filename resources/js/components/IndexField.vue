<template>
    <div v-if="field.url" class="main-container">
        <img :src="field.url"
             @click="showBigImage = true"
             :style="`object-fit: cover; height: ${field.height}px; min-width: ${field.width}px; width: ${field.width}px`"
             :height="field.height"
             :width="field.width"
             class="rounded-full image"/>
        <div class="image-hover-container"
             ref="bigImageContainer"
             v-if="showBigImage">
            <span @click="showBigImage = false; isShowed = false">X</span>
            <img :src="field.url" ref="bigImage"/>
        </div>
    </div>
    <span v-else>Image</span>
</template>

<script>
    export default {
        props: ['viaResource', 'viaResourceId', 'resourceName', 'field'],
        data() {
            return {
                showBigImage: false,
                isShowed: false,
            };
        },
        mounted: function () {
            // Attach event listener to the root vue element
            window.addEventListener('click', this.onClick)
            // Or if you want to affect everything
            // document.addEventListener('click', this.onClick)
        },
        beforeDestroy: function () {
            window.removeEventListener('click', this.onClick)
            // document.removeEventListener('click', this.onClick)
        },
        methods: {
            onClick(event) {
                if (
                    this.isShowed &&
                    event.target !== this.$refs['bigImage'] &&
                    event.target !== this.$refs['bigImageContainer']
                ) {
                    this.showBigImage = this.isShowed = false;
                }
                if (this.showBigImage) {
                    this.isShowed = true;
                }
            },
        },
    }
</script>
<style lang="scss" scoped>
    .main-container {
        .image-hover-container {
            padding: 10px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            width: 900px;
            height: 650px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
            background-color: #eef1f4;
            display: flex;
            align-items: center;
            justify-content: center;
            img {
                max-width: 880px;
                max-height: 630px;
            }
            span {
                position: absolute;
                top: 5px;
                left: 5px;
                text-align: center;
                padding: 5px;
                color: #000000;
                border: solid 1px #000000;
                border-radius: 3px;
                cursor: pointer;
            }
        }
        .image {
            cursor: pointer;
        }
    }
</style>
