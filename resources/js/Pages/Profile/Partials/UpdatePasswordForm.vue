<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">

        <header class="bg-slate-50/80 p-6 border-b border-slate-100">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">
                Actualizar Contraseña
            </h2>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                Asegúrate de que tu cuenta esté usando una contraseña larga y aleatoria para mantenerse segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="p-6 space-y-6">

            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-1">
                    <InputLabel for="current_password" value="Contraseña Actual" class="text-xs font-bold text-slate-700 uppercase ml-1" />
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all outline-none text-sm font-medium"
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.current_password" class="mt-2 text-[10px] font-bold" />
                </div>

                <div class="space-y-1">
                    <InputLabel for="password" value="Nueva Contraseña" class="text-xs font-bold text-slate-700 uppercase ml-1" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all outline-none text-sm font-medium"
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password" class="mt-2 text-[10px] font-bold" />
                </div>

                <div class="space-y-1">
                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="text-xs font-bold text-slate-700 uppercase ml-1" />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all outline-none text-sm font-medium"
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password_confirmation" class="mt-2 text-[10px] font-bold" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <PrimaryButton
                    :disabled="form.processing"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-orange-600/20 transition-all active:scale-95 border-none"
                >
                    Guardar Contraseña
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-green-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        ¡Actualizada con éxito!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
