<template>
  <div class="flex-1 overflow-y-auto" :style="{ maxHeight: maxHeight + 'px' }">
    <!-- Comment list -->
    <div class="space-y-4 p-4">
      <div v-for="(comment, index) in comments" :key="index" class="flex space-x-3">
        <img :src="comment.avatar" class="w-8 h-8 rounded-full" :alt="comment.username" />
        <div class="flex-1">
          <div class="flex items-center space-x-1">
            <span class="font-semibold">{{ comment.username }}</span>
            <span v-if="comment.isVerified" class="text-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </span>
          </div>
          <p class="text-gray-700">{{ comment.text }}</p>
          <div class="text-gray-500 text-xs mt-1 flex space-x-2">
            <span>{{ comment.time }}</span>
            <button class="font-semibold">Like</button>
            <button class="font-semibold">Reply</button>
            <span v-if="comment.likes > 0" class="font-semibold">{{ comment.likes }} likes</span>
          </div>
          
          <!-- Nested replies -->
          <div v-if="comment.replies && comment.replies.length > 0" class="mt-2 pl-4 border-l-2 border-gray-100">
            <div v-for="(reply, replyIndex) in comment.replies" :key="replyIndex" class="flex space-x-3 mt-2">
              <img :src="reply.avatar" class="w-6 h-6 rounded-full" :alt="reply.username" />
              <div>
                <div class="flex items-center space-x-1">
                  <span class="font-semibold">{{ reply.username }}</span>
                  <span v-if="reply.isVerified" class="text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </span>
                </div>
                <p class="text-gray-700">{{ reply.text }}</p>
                <div class="text-gray-500 text-xs mt-1 flex space-x-2">
                  <span>{{ reply.time }}</span>
                  <button class="font-semibold">Like</button>
                  <button class="font-semibold">Reply</button>
                  <span v-if="reply.likes > 0" class="font-semibold">{{ reply.likes }} likes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Comment form component slot -->
      <slot name="comment-form"></slot>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'CommentList',
  props: {
    comments: {
      type: Array,
      default: () => []
    },
    maxHeight: {
      type: Number,
      default: 380
    }
  },
  setup(props) {
    // Component sử dụng props trực tiếp trong template
    return {};
  }
})
</script> 