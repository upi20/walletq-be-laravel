<script setup lang="ts">
import { useConfirmation } from '@/composables/useConfirmation';
import ConfirmationDialog from './ConfirmationDialog.vue';

const { activeDialog, closeDialog } = useConfirmation();

const handleConfirm = () => {
  closeDialog(true);
};

const handleCancel = () => {
  closeDialog(false);
};

const handleClose = () => {
  closeDialog(false);
};
</script>

<template>
  <Teleport to="body">
    <Transition
      name="confirmation"
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <ConfirmationDialog
        v-if="activeDialog"
        :dialog="activeDialog"
        @confirm="handleConfirm"
        @cancel="handleCancel"
        @close="handleClose"
      />
    </Transition>
  </Teleport>
</template>