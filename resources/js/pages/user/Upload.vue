<!-- src/pages/UploadPage.vue -->
<template>
  <div
    class="upload-page container mx-auto p-6 md:p-6 bg-gray-50 min-h-screen pt-20 md:pt-24"
  >
    <div class="max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-8">
        <div class="flex items-center">
          <ButtonBackVue customClass="bg-gradient-text hover: text-white font-bold py-2 px-4 rounded-full" />
          <h1 class="text-2xl ml-2 md:text-3xl font-bold bg-gradient-text-v2">
            Tải ảnh lên
          </h1>
        </div>
        <span class="text-sm text-gray-500 hidden md:inline-block">{{
          uploadStatus
        }}</span>
      </div>

      <div
        v-if="uploadErrors.length > 0"
        class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg"
      >
        <h3 class="text-sm font-semibold text-red-700 mb-2">Lưu ý:</h3>
        <ul class="text-sm text-red-600 list-disc pl-5 space-y-1">
          <li v-for="(error, index) in uploadErrors" :key="index">
            {{ error }}
          </li>
        </ul>
      </div>

      <div class="flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-1/2">
          <div
            class="bg-white p-4 rounded-xl shadow-md border border-gray-100 mb-4">
            <h2
              class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100"
            >
              Tải ảnh
              <span class="text-sm font-normal text-gray-500 ml-2"
                >(tối đa 5 ảnh, mỗi ảnh tối đa 2MB)</span
              >
            </h2>
            <ImageUploader />
          </div>
        </div>

        <div class="w-full lg:w-1/2">
          <ImageInfoForm :featureId="featureId" :featureName="featureName"/>
        </div>
      </div>

      <div class="mt-8 text-center text-sm text-gray-500">
        <p>Hỗ trợ định dạng: JPG, PNG | Kích thước tối đa: 2MB/ảnh</p>
        <p class="mt-2">
          Vui lòng không tải lên những hình ảnh vi phạm bản quyền hoặc nội dung
          không phù hợp
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import ButtonBackVue from "@/components/common/ButtonBack.vue";
import ImageUploader from "@/components/user/Upload/ImageUploader.vue";
import ImageInfoForm from "@/components/user/Upload/ImageInfoForm.vue";
import { computed, provide } from "vue";
import { useImageUpload } from "@/composables/user/useImageUpload";
import { useRoute } from "vue-router";

export default {
  components: {
    ImageUploader,
    ImageInfoForm,
    ButtonBackVue
  },
  setup() {
    // Tạo một instance duy nhất của useImageUpload
    const imageUploadInstance = useImageUpload();
    const route = useRoute();
    const featureId = computed(() => route.query.featureId);
    const featureName = computed(() => route.query.featureName);
    const { files, uploadErrors, totalFiles, remainingSlots} = imageUploadInstance;

    // Cung cấp instance này cho các component con
    provide('imageUploadInstance', imageUploadInstance);

    const uploadStatus = computed(() => {
      if (totalFiles.value === 0) return "Chưa có ảnh nào được tải lên";
      return `Đã tải lên ${totalFiles.value}/5 ảnh, còn lại ${remainingSlots.value} ảnh`;
    });

    return {
      files,
      uploadErrors,
      uploadStatus,
      featureId,
      featureName
    };
  }
}
</script>