<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Textarea from '@/components/ui/textarea.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, X } from 'lucide-vue-next';

interface Props {
    typeOptions: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Attributes', href: '/admin/product-attributes' },
    { title: 'Create Attribute', href: '/admin/product-attributes/create' },
];

const form = useForm({
    name: '',
    description: '',
    type: 'text',
    options: [] as string[],
    is_required: false,
    is_active: true,
});

const newOption = ref('');

const addOption = () => {
    if (newOption.value.trim() && !form.options.includes(newOption.value.trim())) {
        form.options.push(newOption.value.trim());
        newOption.value = '';
    }
};

const removeOption = (index: number) => {
    form.options.splice(index, 1);
};

const submit = () => {
    if (!form.name.trim()) {
        alert('Please enter an attribute name');
        return;
    }
    
    form.post('/admin/product-attributes', {
        onError: (errors: any) => {
            console.log('Validation errors:', errors);
        },
        onSuccess: () => {
            console.log('Attribute created successfully');
        },
    });
};
</script>

<template>
    <Head title="Create Product Attribute" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Product Attribute</h1>
                <p class="text-gray-600 dark:text-gray-400">Add a new product attribute for categorizing products</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Attribute Information</CardTitle>
                        <CardDescription>
                            Basic information about the product attribute
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Attribute Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Color, Size, Material"
                                :class="{ 'border-red-500': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Type -->
                        <div class="space-y-2">
                            <Label for="type">Attribute Type *</Label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.type }"
                                required
                            >
                                <option v-for="(label, value) in typeOptions" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <p v-if="form.errors.type" class="text-sm text-red-600">{{ form.errors.type }}</p>
                            <p class="text-sm text-gray-500">
                                <span v-if="form.type === 'text'">Text attributes allow free-form text input</span>
                                <span v-else-if="form.type === 'number'">Number attributes allow numeric values only</span>
                                <span v-else-if="form.type === 'color'">Color attributes allow color picker or hex values</span>
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Brief description of what this attribute represents"
                                rows="3"
                                :class="form.errors.description ? 'border-red-500' : ''"
                            />
                            <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Predefined Options (for text and color types) -->
                        <div v-if="form.type === 'text' || form.type === 'color'" class="space-y-2">
                            <Label>Predefined Options (Optional)</Label>
                            <p class="text-sm text-gray-500">Add predefined options that users can choose from</p>
                            
                            <!-- Add new option -->
                            <div class="flex gap-2">
                                <Input
                                    v-model="newOption"
                                    type="text"
                                    placeholder="Add an option..."
                                    class="flex-1"
                                    @keyup.enter="addOption"
                                />
                                <Button type="button" @click="addOption" size="sm">
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>

                            <!-- Display existing options -->
                            <div v-if="form.options.length > 0" class="space-y-2">
                                <div
                                    v-for="(option, index) in form.options"
                                    :key="index"
                                    class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-md"
                                >
                                    <span class="flex-1 text-sm">{{ option }}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="removeOption(index)"
                                        class="h-6 w-6 p-0 text-red-600 hover:text-red-700"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_required"
                                    type="checkbox"
                                    v-model="form.is_required"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <div class="space-y-1">
                                    <Label for="is_required">Required Attribute</Label>
                                    <p class="text-sm text-gray-500">
                                        If enabled, this attribute must be specified for all products
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <div class="space-y-1">
                                    <Label for="is_active">Active Status</Label>
                                    <p class="text-sm text-gray-500">
                                        Only active attributes will be available for products
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex items-center gap-3 pt-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Attribute' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit('/admin/product-attributes')"
                            >
                                Cancel
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
