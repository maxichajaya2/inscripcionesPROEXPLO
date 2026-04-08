<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">

        <header class="bg-red-50/50 p-6 border-b border-red-100">
            <h2 class="text-xl font-black text-red-800 uppercase tracking-tight">
                Eliminar Cuenta
            </h2>

            <p class="mt-1 text-sm text-red-600/80 font-medium">
                Una vez que se elimine tu cuenta, todos sus recursos y datos se borrarán permanentemente. Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.
            </p>
        </header>

        <div class="p-6">
            <DangerButton
                @click="confirmUserDeletion"
                class="bg-red-600 hover:bg-red-700 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-red-600/20 transition-all active:scale-95 border-none"
            >
                Eliminar Cuenta
            </DangerButton>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tight">
                    ¿Estás seguro de que quieres eliminar tu cuenta?
                </h2>

                <p class="mt-2 text-sm text-slate-500 font-medium">
                    Una vez que se elimine tu cuenta, todos sus recursos y datos se borrarán permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Contraseña" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none text-sm font-medium"
                        placeholder="Ingresa tu contraseña"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2 text-[10px] font-bold" />
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <SecondaryButton
                        @click="closeModal"
                        class="rounded-xl px-6 py-2.5 font-bold text-xs uppercase tracking-widest transition-all active:scale-95"
                    >
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="bg-red-600 hover:bg-red-700 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-red-600/20 transition-all active:scale-95 border-none"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Eliminar Cuenta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
