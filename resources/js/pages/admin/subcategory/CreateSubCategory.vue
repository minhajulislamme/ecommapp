<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Upload, X } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Sub-Category Management', href: '/admin/subcategories' },
    { title: 'Create Sub-Category', href: '/admin/subcategories/create' },
];

const form = useForm({
    category_id: '',
    name: '',
    description: '',
    image: null as File | null,
    is_active: true,
});

const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Check for category_id in URL params
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category_id');
    if (categoryId && props.categories.find(c => c.id.toString() === categoryId)) {
        form.category_id = categoryId;
    }
});

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        form.image = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    // Debug: log form data before submission
    console.log('Form data before submission:', {
        category_id: form.category_id,
        name: form.name,
        description: form.description,
        is_active: form.is_active,
        image: form.image
    });
    
    // Basic client-side validation
    if (!form.name.trim()) {
        alert('Please enter a sub-category name');
        return;
    }
    
    if (!form.category_id) {
        alert('Please select a parent category');
        return;
    }
    
    form.post('/admin/subcategories', {
        forceFormData: true,
        onError: (errors) => {
            console.log('Validation errors:', errors);
        },
        onSuccess: () => {
            console.log('Sub-category created successfully');
        },
    });
};
</script>

<template>
    <Head title="Create Sub-Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Sub-Category</h1>
                <p class="text-gray-600 dark:text-gray-400">Add a new product sub-category</p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Sub-Category Information</CardTitle>
                        <CardDescription>
                            Fill in the details for the new sub-category
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Category Selection -->
                        <div class="space-y-2">
                            <Label for="category_id">Parent Category</Label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.category_id }"
                                required
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in props.categories" :key="category.id" :value="category.id.toString()">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="text-sm text-red-600">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Sub-Category Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Enter sub-category name"
                                :class="{ 'border-red-500': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Enter sub-category description (optional)"
                                rows="3"
                                class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Image Upload -->
                        <div class="space-y-2">
                            <Label>Sub-Category Image</Label>
                            <div class="space-y-4">
                                <div
                                    v-if="!imagePreview"
                                    class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-gray-400 dark:hover:border-gray-500 transition-colors cursor-pointer"
                                    @click="fileInput?.click()"
                                >
                                    <div class="text-center">
                                        <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                Click to upload image
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                PNG, JPG, GIF up to 2MB
                                            </p>
                                        </div>
                                    </div>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleImageUpload"
                                    />
                                </div>

                                <div v-if="imagePreview" class="relative">
                                    <img
                                        :src="imagePreview"
                                        alt="Preview"
                                        class="h-32 w-32 object-cover rounded-lg border"
                                    />
                                    <Button
                                        type="button"
                                        @click="removeImage"
                                        variant="destructive"
                                        size="sm"
                                        class="absolute -top-2 -right-2 h-6 w-6 rounded-full p-0"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <p v-if="form.errors.image" class="text-sm text-red-600">{{ form.errors.image }}</p>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <Label for="is_active">Active Status</Label>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Whether this sub-category is active and visible
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center gap-3 pt-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Sub-Category' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit('/admin/subcategories')"
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
