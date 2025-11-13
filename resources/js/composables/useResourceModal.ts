import { ref, type Ref } from 'vue';

export type ModalMode = 'create' | 'edit' | 'view';

export function useResourceModal<T = Record<string, any>>() {
  const isOpen = ref(false);
  const mode = ref<ModalMode>('create');
  const entity = ref<T | null>(null) as Ref<T | null>;

  function open(nextMode: ModalMode, nextEntity?: T | null) {
    mode.value = nextMode;
    entity.value = (nextEntity ?? null) as T | null;
    isOpen.value = true;
  }

  function close() {
    isOpen.value = false;
  }

  function onSuccess() {
    // convention: close and let caller refresh table
    isOpen.value = false;
  }

  return { isOpen, mode, entity, open, close, onSuccess };
}
