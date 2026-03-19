<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const page = usePage();
// read filters safely from the Inertia page props (fall back to empty object)
const filters = page.props?.filters ?? {};
const search = ref(filters.search ?? '');
const perPage = ref(filters.per_page ?? 20);
const csrf = page.props?.csrf ?? '';

const apply = () => {
  router.get('/departments', { search: search.value, per_page: perPage.value }, { replace: true, preserveState: true });
};

const exportUrl = computed(() => `/departments?search=${encodeURIComponent(search.value)}`);
</script>

<template>
  <Head title="Departments" />
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Departments</h1>
        <div class="flex gap-2">
          <Button variant="outline" size="sm"><a :href="exportUrl">Export</a></Button>
          <Button size="sm"><Link href="/departments/create">Add Department</Link></Button>
        </div>
      </div>

      <div class="mb-4 flex gap-2">
        <Input v-model="search" placeholder="Search departments..." />
        <Button variant="outline" @click="apply">Search</Button>
      </div>

      <div class="grid gap-3">
        <div v-if="!(page.props?.departments?.data?.length)" class="text-muted-foreground">No departments found</div>
        <div v-for="d in page.props?.departments?.data ?? []" :key="d.id" class="rounded border p-3 flex justify-between items-center">
          <div>
            <div class="font-semibold">{{ d.name }}</div>
            <div class="text-sm text-muted-foreground">{{ d.description }}</div>
            <div class="text-sm">Head: {{ d.head_of_department ?? '—' }}</div>
          </div>
          <div class="flex gap-2">
            <Link :href="`/departments/${d.id}/edit`" class="text-blue-600">Edit</Link>
            <form :action="`/departments/${d.id}`" method="post">
              <input type="hidden" name="_method" value="delete" />
              <input type="hidden" name="_token" :value="csrf" />
              <button type="submit" class="text-red-600">Delete</button>
            </form>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <!-- pagination simple -->
        <div class="flex items-center justify-between">
          <div>Showing {{ page.props?.departments?.from ?? 0 }} to {{ page.props?.departments?.to ?? 0 }} of {{ page.props?.departments?.total ?? 0 }}</div>
          <div class="flex gap-2">
            <button v-if="page.props?.departments?.prev_page_url" @click.prevent="router.get(page.props.departments.prev_page_url)" class="px-3 py-1 border rounded">Prev</button>
            <button v-if="page.props?.departments?.next_page_url" @click.prevent="router.get(page.props.departments.next_page_url)" class="px-3 py-1 border rounded">Next</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
