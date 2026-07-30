# Admin Panel & Auth Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build admin CRUD panel (9 modules) + split-screen auth page for Pemanfaatan Aset Pemko Medan.

**Architecture:** Nuxt 3 frontend (Vue 3) with Tailwind CSS calls Laravel 13 Sanctum API. Admin layout uses dashboard-card navigation + sidebar-after-enter hybrid. Auth uses split-screen layout with form + Medan landmark visual.

**Tech Stack:** Nuxt 3, Vue 3, Tailwind CSS, Laravel 13, PostgreSQL 15, Sanctum

## Global Constraints
- Font minimum 16px on all inputs/labels (PNS readability)
- Primary color: #0D9488 (teal-600), no new frontend libraries
- All backend API routes use auth:sanctum middleware (except login/register)
- File uploads to storage/app/public/
- No tests required (project has no test infrastructure)

---

### Task 1: Backend — Roles Table Migration, Model, Controller, Routes

**Files:**
- Create: `backend/database/migrations/2024_01_01_000012_create_roles_table.php`
- Create: `backend/app/Models/Role.php`
- Create: `backend/app/Http/Controllers/Api/RoleController.php`
- Modify: `backend/routes/api.php`
- Modify: `backend/database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Produces: `Role` model, `RoleController` with apiResource, route `/api/roles`
- Consumes: `User` model (add relationship in Task 2)

- [ ] **Create roles migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
```

- [ ] **Create Role model**

`backend/app/Models/Role.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'guard_name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
```

- [ ] **Create RoleController**

`backend/app/Http/Controllers/Api/RoleController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Role::withCount('users')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'guard_name' => 'nullable|string|max:255',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return response()->json($role, 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->loadCount('users'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable|string|max:255',
        ]);

        $role->update($validated);
        return response()->json($role);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => ['Role masih memiliki pengguna. Tidak bisa dihapus.'],
            ]);
        }
        $role->delete();
        return response()->json(['message' => 'Role berhasil dihapus.']);
    }
}
```

- [ ] **Register route**

Add to `backend/routes/api.php` inside the `auth:sanctum` group:
```php
Route::apiResource('roles', RoleController::class);
```

- [ ] **Update DatabaseSeeder to seed roles**

Add to `backend/database/seeders/DatabaseSeeder.php` within the `run()` method:
```php
DB::table('roles')->insert([
    ['name' => 'Super Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'Petugas', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
]);
```

---

### Task 2: Backend — Add role_id to users, update User/AuthController

**Files:**
- Create: `backend/database/migrations/2024_01_01_000013_add_role_id_to_users_table.php`
- Modify: `backend/app/Models/User.php`
- Modify: `backend/app/Http/Controllers/Api/AuthController.php`
- Modify: `backend/database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Consumes: `Role` model (from Task 1)
- Produces: `User` with `role` relationship, AuthController returns role data

- [ ] **Create migration to add role_id**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
```

- [ ] **Update User model**

Add to `backend/app/Models/User.php`:
```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Add method:
public function role(): BelongsTo
{
    return $this->belongsTo(Role::class);
}
```

Also add `'role_id'` to the Fillable attribute:
```php
#[Fillable(['name', 'email', 'password', 'role_id'])]
```

- [ ] **Update AuthController to return role**

In `backend/app/Http/Controllers/Api/AuthController.php`, modify `login()` and `register()` methods to load role:

In `login()`, after `$user = User::where('email', $request->email)->firstOrFail();`:
```php
$user->load('role');
```

In `register()`, after `$user = User::create([...])`:
```php
$user->load('role');
```

Also update `user()` method:
```php
public function user(Request $request): JsonResponse
{
    return response()->json($request->user()->load('role'));
}
```

- [ ] **Update DatabaseSeeder to assign roles to seed users**

In `backend/database/seeders/DatabaseSeeder.php`, after creating users:
```php
$superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first()->id;
$petugasRole = DB::table('roles')->where('name', 'Petugas')->first()->id;

DB::table('users')->where('email', 'admin@pemkomedan.go.id')->update(['role_id' => $superAdminRole]);
DB::table('users')->where('email', 'petugas@pemkomedan.go.id')->update(['role_id' => $petugasRole]);
```

---

### Task 3: Frontend — Shared UI Components

**Files:**
- Create: `frontend/components/ui/Button.vue`
- Create: `frontend/components/ui/Input.vue`
- Create: `frontend/components/ui/Select.vue`
- Create: `frontend/components/ui/Card.vue`
- Create: `frontend/components/ui/Modal.vue`
- Create: `frontend/components/ui/Toast.vue`
- Create: `frontend/composables/useToast.ts`

**Design tokens (inline Tailwind classes):**
- Primary button: `bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg px-6 py-3`
- Input: `w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-base`
- Card: `bg-white rounded-xl border border-slate-200 p-6`
- Modal: centered overlay with backdrop blur

- [ ] **Create Button.vue**

```vue
<template>
  <button
    :class="[
      'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed',
      variant === 'primary' ? 'bg-teal-600 hover:bg-teal-700 text-white shadow-sm' : '',
      variant === 'secondary' ? 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300' : '',
      variant === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white shadow-sm' : '',
      variant === 'ghost' ? 'hover:bg-slate-100 text-slate-600' : '',
      size === 'sm' ? 'px-3 py-2 text-sm' : size === 'lg' ? 'px-6 py-3 text-base' : 'px-4 py-2.5 text-sm',
    ]"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
defineProps<{
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
}>()
</script>
```

- [ ] **Create Input.vue**

```vue
<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1.5">{{ label }}</label>
    <div v-if="prefixIcon || suffixIcon" class="relative">
      <div v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
        <span v-html="prefixIcon" />
      </div>
      <input
        :class="[
          'w-full px-4 py-3 border rounded-lg text-base transition-all duration-200 outline-none',
          'focus:ring-2 focus:ring-teal-500 focus:border-teal-500',
          error ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white',
          prefixIcon ? 'pl-10' : '',
          suffixIcon ? 'pr-10' : '',
        ]"
        v-bind="$attrs"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
      <div v-if="suffixIcon" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
        <span v-html="suffixIcon" />
      </div>
    </div>
    <input
      v-else
      :class="[
        'w-full px-4 py-3 border rounded-lg text-base transition-all duration-200 outline-none',
        'focus:ring-2 focus:ring-teal-500 focus:border-teal-500',
        error ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white',
      ]"
      v-bind="$attrs"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="hint && !error" class="mt-1 text-sm text-slate-500">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue?: string | number
  label?: string
  error?: string
  hint?: string
  prefixIcon?: string
  suffixIcon?: string
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
```

- [ ] **Create Select.vue**

```vue
<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1.5">{{ label }}</label>
    <select
      :class="[
        'w-full px-4 py-3 border rounded-lg text-base transition-all duration-200 outline-none appearance-none bg-white',
        'focus:ring-2 focus:ring-teal-500 focus:border-teal-500',
        error ? 'border-red-400 bg-red-50' : 'border-slate-300',
      ]"
      :value="modelValue"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      v-bind="$attrs"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue?: string | number
  label?: string
  error?: string
  placeholder?: string
  options: { value: string | number; label: string }[]
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
```

- [ ] **Create Card.vue**

```vue
<template>
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm" :class="padding ? 'p-6' : ''">
    <div v-if="title" class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
      <slot name="action" />
    </div>
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
  padding?: boolean
}>()
</script>
```

- [ ] **Create Modal.vue**

```vue
<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div v-if="title" class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
            <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-6">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{
  show: boolean
  title?: string
}>()

defineEmits<{
  close: []
}>()
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-active > div:last-child, .modal-leave-active > div:last-child { transition: transform 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from > div:last-child { transform: scale(0.95) translateY(10px); }
.modal-leave-to > div:last-child { transform: scale(0.95) translateY(10px); }
</style>
```

- [ ] **Create Toast.vue + useToast.ts**

```vue
<!-- frontend/components/ui/Toast.vue -->
<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2">
      <TransitionGroup name="toast">
        <div
          v-for="t in toasts"
          :key="t.id"
          :class="[
            'px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium flex items-center gap-2 min-w-[280px] max-w-md',
            t.type === 'success' ? 'bg-emerald-600' : t.type === 'error' ? 'bg-red-600' : 'bg-slate-800',
          ]"
        >
          <span v-html="t.message" />
          <button @click="dismiss(t.id)" class="ml-auto text-white/70 hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useToast } from '~/composables/useToast'
const { toasts, dismiss } = useToast()
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateX(100%); opacity: 0; }
</style>
```

```ts
// frontend/composables/useToast.ts
import { ref } from 'vue'

interface Toast {
  id: number
  message: string
  type: 'success' | 'error' | 'info'
}

const toasts = ref<Toast[]>([])
let nextId = 0

export function useToast() {
  function show(message: string, type: Toast['type'] = 'info', duration = 4000) {
    const id = ++nextId
    toasts.value.push({ id, message, type })
    setTimeout(() => dismiss(id), duration)
  }

  function dismiss(id: number) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  return { toasts, show, dismiss }
}
```

---

### Task 4: Frontend — Admin Layout (Sidebar + TopBar) + Auth Composables

**Files:**
- Create: `frontend/layouts/admin.vue`
- Create: `frontend/components/admin/Sidebar.vue`
- Create: `frontend/components/admin/TopBar.vue`
- Create: `frontend/composables/useAuth.ts`
- Create: `frontend/composables/useApi.ts`

- [ ] **Create useApi composable**

```ts
// frontend/composables/useApi.ts
import { useRuntimeConfig } from '#app'
import { useAuth } from './useAuth'

export function useApi() {
  const config = useRuntimeConfig()
  const baseURL = config.public?.apiBase || 'http://localhost:8000'

  function headers(): Record<string, string> {
    const h: Record<string, string> = { 'Content-Type': 'application/json', 'Accept': 'application/json' }
    const token = useAuth().token.value
    if (token) h['Authorization'] = `Bearer ${token}`
    return h
  }

  async function get<T = any>(path: string): Promise<T> {
    const res = await fetch(`${baseURL}/api${path}`, { headers: headers() })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e)) }
    return res.json()
  }

  async function post<T = any>(path: string, body?: any): Promise<T> {
    const h = headers()
    const res = await fetch(`${baseURL}/api${path}`, { method: 'POST', headers: h, body: body ? JSON.stringify(body) : undefined })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e.errors || e)) }
    return res.json()
  }

  async function put<T = any>(path: string, body: any): Promise<T> {
    const h = headers()
    const res = await fetch(`${baseURL}/api${path}`, { method: 'PUT', headers: h, body: JSON.stringify(body) })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e.errors || e)) }
    return res.json()
  }

  async function del<T = any>(path: string): Promise<T> {
    const h = headers()
    const res = await fetch(`${baseURL}/api${path}`, { method: 'DELETE', headers: h })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e)) }
    return res.json()
  }

  async function upload<T = any>(path: string, formData: FormData): Promise<T> {
    const h: Record<string, string> = { 'Accept': 'application/json' }
    const token = useAuth().token.value
    if (token) h['Authorization'] = `Bearer ${token}`
    const res = await fetch(`${baseURL}/api${path}`, { method: 'POST', headers: h, body: formData })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e.errors || e)) }
    return res.json()
  }

  return { get, post, put, del, upload, baseURL }
}
```

- [ ] **Create useAuth composable**

```ts
// frontend/composables/useAuth.ts
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from './useApi'

const token = ref<string | null>(null)
const user = ref<any>(null)
const initialized = ref(false)

export function useAuth() {
  const api = useApi()

  if (!initialized.value) {
    token.value = localStorage.getItem('token')
    initialized.value = true
  }

  const isLoggedIn = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const res = await api.post('/login', { email, password })
    token.value = res.token
    user.value = res.user
    localStorage.setItem('token', res.token)
    return res
  }

  async function logout() {
    await api.post('/logout')
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  async function fetchUser() {
    try {
      user.value = await api.get('/user')
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
    }
  }

  return { token, user, isLoggedIn, login, logout, fetchUser }
}
```

- [ ] **Create Sidebar component**

```vue
<!-- frontend/components/admin/Sidebar.vue -->
<template>
  <aside :class="[
    'fixed left-0 top-0 h-full bg-white border-r border-slate-200 z-40 transition-all duration-300 flex flex-col',
    collapsed ? 'w-16' : 'w-60'
  ]">
    <div class="h-16 flex items-center px-4 border-b border-slate-200">
      <button @click="$emit('toggle')" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="collapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'" />
        </svg>
      </button>
      <span v-if="!collapsed" class="ml-3 font-semibold text-slate-900 text-sm">Menu</span>
    </div>
    <nav class="flex-1 py-4 overflow-y-auto">
      <NuxtLink
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'flex items-center gap-3 px-4 py-3 mx-2 rounded-lg text-sm font-medium transition-all',
          isActive(item.to) ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
        ]"
        :title="collapsed ? item.label : ''"
      >
        <span class="w-5 h-5 flex-shrink-0" v-html="item.icon" />
        <span v-if="!collapsed">{{ item.label }}</span>
      </NuxtLink>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'

const props = defineProps<{ collapsed: boolean }>()
defineEmits<{ toggle: [] }>()

const route = useRoute()

function isActive(path: string) {
  if (path === '/admin') return route.path === '/admin'
  return route.path.startsWith(path)
}

const menuItems = [
  { to: '/admin', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', label: 'Dashboard' },
  { to: '/admin/opd', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', label: 'OPD' },
  { to: '/admin/kategori-aset', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', label: 'Kategori' },
  { to: '/admin/gis-layer', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>', label: 'GIS Layer' },
  { to: '/admin/jenis-pemanfaatan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', label: 'Jenis' },
  { to: '/admin/pihak-ketiga', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', label: 'Pihak Ketiga' },
  { to: '/admin/aset', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', label: 'Aset' },
  { to: '/admin/pemanfaatan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', label: 'Pemanfaatan' },
  { to: '/admin/users', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>', label: 'Users' },
  { to: '/admin/roles', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>', label: 'Roles' },
]
</script>
```

- [ ] **Create TopBar component**

```vue
<!-- frontend/components/admin/TopBar.vue -->
<template>
  <header :class="['h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 transition-all duration-300', sidebarCollapsed ? 'ml-16' : 'ml-60']">
    <div class="flex items-center gap-2 text-sm text-slate-500">
      <NuxtLink to="/admin" class="hover:text-teal-600">Dashboard</NuxtLink>
      <template v-for="(crumb, i) in breadcrumbs" :key="i">
        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span v-if="i === breadcrumbs.length - 1" class="text-slate-900 font-medium">{{ crumb.label }}</span>
        <NuxtLink v-else :to="crumb.to" class="hover:text-teal-600">{{ crumb.label }}</NuxtLink>
      </template>
    </div>
    <div class="flex items-center gap-4">
      <span class="text-sm text-slate-500">
        {{ user?.name }} 
        <span class="text-xs bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full ml-1">{{ user?.role?.name || '—' }}</span>
      </span>
      <button @click="handleLogout" class="text-sm text-slate-400 hover:text-red-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

const props = defineProps<{ sidebarCollapsed: boolean }>()
const route = useRoute()
const router = useRouter()
const { user, logout } = useAuth()

const breadcrumbs = computed(() => {
  const parts = route.path.split('/').filter(Boolean)
  if (parts.length <= 1) return []
  const crumbs: { label: string; to: string }[] = []
  let path = ''
  for (const p of parts) {
    path += '/' + p
    if (p === 'admin' || p === 'auth') continue
    const label = p.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
    crumbs.push({ label, to: path })
  }
  return crumbs
})

async function handleLogout() {
  await logout()
  router.push('/auth/login')
}
</script>
```

- [ ] **Create admin layout**

```vue
<!-- frontend/layouts/admin.vue -->
<template>
  <div class="min-h-screen bg-slate-50">
    <AdminSidebar :collapsed="sidebarCollapsed" @toggle="sidebarCollapsed = !sidebarCollapsed" />
    <AdminTopBar :sidebar-collapsed="sidebarCollapsed" />
    <main :class="['pt-16 min-h-screen transition-all duration-300', sidebarCollapsed ? 'ml-16' : 'ml-60']">
      <div class="p-6">
        <slot />
      </div>
    </main>
    <UiToast />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const sidebarCollapsed = ref(false)
</script>
```

---

### Task 5: Frontend — Auth Login Page

**Files:**
- Create: `frontend/pages/auth/login.vue`
- Modify: `frontend/app.vue`

- [ ] **Create login page**

```vue
<template>
  <div class="min-h-screen flex">
    <!-- Left: Form -->
    <div class="flex-1 flex items-center justify-center p-8 bg-white">
      <div class="w-full max-w-md">
        <div class="text-center mb-8">
          <div class="w-16 h-16 mx-auto mb-4 bg-teal-100 rounded-2xl flex items-center justify-center">
            <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-900">Selamat Datang</h1>
          <p class="text-slate-500 mt-1">Sistem Pemanfaatan Aset Pemko Medan</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <UiInput
            v-model="email"
            label="Email"
            type="email"
            placeholder="admin@pemkomedan.go.id"
            :error="errors.email"
            prefix-icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'/></svg>"
          />

          <div>
            <UiInput
              v-model="password"
              label="Password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Masukkan password"
              :error="errors.password"
              prefix-icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'/></svg>"
              :suffix-icon="showPassword ? eyeOffIcon : eyeIcon"
              @click:suffix="showPassword = !showPassword"
            />
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="remember" class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
              <span class="text-sm text-slate-600">Ingat Saya</span>
            </label>
            <a href="#" class="text-sm text-teal-600 hover:text-teal-700 font-medium">Lupa Password?</a>
          </div>

          <UiButton type="submit" variant="primary" size="lg" class="w-full" :disabled="loading">
            {{ loading ? 'Memproses...' : 'Masuk' }}
          </UiButton>
        </form>

        <p v-if="loginError" class="mt-4 text-sm text-red-600 text-center bg-red-50 rounded-lg p-3">{{ loginError }}</p>
      </div>
    </div>

    <!-- Right: Visual Panel -->
    <div class="hidden lg:flex flex-1 relative bg-gradient-to-br from-teal-700 to-teal-900 items-center justify-center overflow-hidden">
      <img v-if="landmarkImg" :src="landmarkImg" alt="Medan Landmark" class="absolute inset-0 w-full h-full object-cover opacity-30" />
      <div class="absolute inset-0 bg-gradient-to-br from-teal-800/70 to-teal-950/80" />
      <div class="relative z-10 text-center px-12">
        <div class="w-24 h-24 mx-auto mb-6 bg-white/10 backdrop-blur rounded-3xl flex items-center justify-center">
          <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-white mb-3">PEMKO MEDAN</h2>
        <p class="text-teal-200 text-lg">Kelola Aset Daerah<br/>untuk Medan Berkah</p>
        <div class="mt-8 flex justify-center gap-2">
          <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse" />
          <span class="w-2 h-2 rounded-full bg-teal-400/60" />
          <span class="w-2 h-2 rounded-full bg-teal-400/30" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

definePageMeta({ layout: false })

const router = useRouter()
const { login, fetchUser } = useAuth()

const email = ref('')
const password = ref('')
const remember = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const loginError = ref('')
const errors = ref<Record<string, string>>({})

const landmarkImg = ref('') // ponytail: add actual Medan landmark image URL if available

const eyeIcon = '<svg class="w-5 h-5 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
const eyeOffIcon = '<svg class="w-5 h-5 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>'

async function handleLogin() {
  loading.value = true
  loginError.value = ''
  errors.value = {}

  try {
    await login(email.value, password.value)
    await fetchUser()
    router.push('/admin')
  } catch (e: any) {
    loginError.value = e.message || 'Email atau password salah'
  } finally {
    loading.value = false
  }
}
</script>
```

- [ ] **Update app.vue to handle auth redirect**

```vue
<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()
if (auth.token.value) {
  auth.fetchUser()
}
</script>
```

---

### Task 6: Frontend — Dashboard Page

**Files:**
- Create: `frontend/pages/admin/index.vue`

- [ ] **Create dashboard page**

```vue
<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Selamat Datang, {{ auth.user?.name || 'Pengguna' }}</h1>
      <p class="text-slate-500 mt-1">Kelola data aset dan pemanfaatan daerah Kota Medan</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <NuxtLink
        v-for="card in cards"
        :key="card.to"
        :to="card.to"
        class="bg-white rounded-xl border border-slate-200 p-6 hover:shadow-md hover:border-teal-300 transition-all duration-200 group"
      >
        <div class="flex items-start gap-4">
          <div :class="['w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0', card.bg]">
            <span class="w-6 h-6" :class="card.iconColor" v-html="card.icon" />
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-slate-900 group-hover:text-teal-600 transition-colors">{{ card.label }}</h3>
            <p class="text-sm text-slate-500 mt-0.5">{{ card.desc }}</p>
          </div>
          <svg class="w-5 h-5 text-slate-300 group-hover:text-teal-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

definePageMeta({ layout: 'admin' })

const auth = useAuth()

const cards = [
  { to: '/admin/opd', label: 'OPD', desc: 'Organisasi Perangkat Daerah', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', bg: 'bg-blue-50', iconColor: 'text-blue-600' },
  { to: '/admin/kategori-aset', label: 'Kategori Aset', desc: 'KIB A (Tanah) & KIB C (Gedung)', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', bg: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { to: '/admin/gis-layer', label: 'GIS Layer', desc: 'Layer peta aset daerah', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>', bg: 'bg-violet-50', iconColor: 'text-violet-600' },
  { to: '/admin/jenis-pemanfaatan', label: 'Jenis Pemanfaatan', desc: 'Sewa, Pinjam Pakai, KSP, BGS, BSG', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', bg: 'bg-amber-50', iconColor: 'text-amber-600' },
  { to: '/admin/pihak-ketiga', label: 'Pihak Ketiga', desc: 'Mitra pemanfaatan aset', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', bg: 'bg-rose-50', iconColor: 'text-rose-600' },
  { to: '/admin/aset', label: 'Aset', desc: 'Data aset tanah & bangunan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', bg: 'bg-cyan-50', iconColor: 'text-cyan-600' },
  { to: '/admin/pemanfaatan', label: 'Pemanfaatan', desc: 'Pemanfaatan aset oleh pihak ketiga', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', bg: 'bg-teal-50', iconColor: 'text-teal-600' },
  { to: '/admin/users', label: 'Users', desc: 'Pengguna sistem', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>', bg: 'bg-indigo-50', iconColor: 'text-indigo-600' },
  { to: '/admin/roles', label: 'Roles', desc: 'Hak akses pengguna', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>', bg: 'bg-orange-50', iconColor: 'text-orange-600' },
]
</script>
```

---

### Task 7: Frontend — Reusable CRUD Table Component

**Files:**
- Create: `frontend/components/admin/DataTable.vue`
- Create: `frontend/components/admin/FormModal.vue`

- [ ] **Create DataTable.vue**

```vue
<template>
  <UiCard>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
      <div class="relative w-full sm:w-72">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          :placeholder="searchPlaceholder"
          class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
          @input="$emit('search', searchQuery)"
        />
      </div>
      <UiButton variant="primary" @click="$emit('create')">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        {{ createLabel }}
      </UiButton>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-slate-200">
            <th v-for="col in columns" :key="col.key" class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">
              {{ col.label }}
            </th>
            <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in data" :key="row.id" class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
            <td v-for="col in columns" :key="col.key" class="py-3 px-4 text-sm text-slate-700">
              <slot :name="`cell-${col.key}`" :row="row" :value="getNestedValue(row, col.key)">
                {{ getNestedValue(row, col.key) }}
              </slot>
            </td>
            <td class="py-3 px-4 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="$emit('edit', row)" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-teal-600 transition-colors" title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button @click="$emit('delete', row)" class="p-2 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!data.length">
            <td :colspan="columns.length + 1" class="py-12 text-center text-slate-400">
              <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              Tidak ada data
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="flex items-center justify-between pt-4 border-t border-slate-200 mt-4">
      <p class="text-sm text-slate-500">Halaman {{ page }} dari {{ totalPages }}</p>
      <div class="flex gap-1">
        <button :disabled="page <= 1" @click="$emit('page-change', page - 1)" class="px-3 py-1.5 rounded text-sm border border-slate-300 hover:bg-slate-50 disabled:opacity-50">
          Sebelumnya
        </button>
        <button :disabled="page >= totalPages" @click="$emit('page-change', page + 1)" class="px-3 py-1.5 rounded text-sm border border-slate-300 hover:bg-slate-50 disabled:opacity-50">
          Berikutnya
        </button>
      </div>
    </div>
  </UiCard>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  columns: { key: string; label: string }[]
  data: any[]
  page?: number
  totalPages?: number
  searchPlaceholder?: string
  createLabel?: string
  loading?: boolean
}>()

defineEmits<{
  search: [query: string]
  create: []
  edit: [row: any]
  delete: [row: any]
  pageChange: [page: number]
}>()

const searchQuery = ref('')

function getNestedValue(obj: any, path: string): any {
  return path.split('.').reduce((acc, part) => acc?.[part], obj) ?? '—'
}
</script>
```

- [ ] **Create FormModal.vue**

```vue
<template>
  <UiModal :show="show" :title="title" @close="$emit('close')">
    <form @submit.prevent="$emit('submit')" class="space-y-4">
      <slot />
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
        <UiButton variant="secondary" @click="$emit('close')" type="button">Batal</UiButton>
        <UiButton variant="primary" type="submit" :disabled="loading">
          {{ loading ? 'Menyimpan...' : 'Simpan' }}
        </UiButton>
      </div>
    </form>
  </UiModal>
</template>

<script setup lang="ts">
defineProps<{
  show: boolean
  title: string
  loading?: boolean
}>()

defineEmits<{
  close: []
  submit: []
}>()
</script>
```

---

### Task 8: Frontend — Simple CRUD Modules (OPD, GIS Layer, Jenis Pemanfaatan, Pihak Ketiga, Roles)

**Files:**
- Create: `frontend/pages/admin/opd.vue`
- Create: `frontend/pages/admin/gis-layer.vue`
- Create: `frontend/pages/admin/jenis-pemanfaatan.vue`
- Create: `frontend/pages/admin/pihak-ketiga.vue`
- Create: `frontend/pages/admin/roles.vue`

Each page follows the same pattern:

```vue
<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">[Module Name]</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari..."
      create-label="Tambah [Module]"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    >
      <!-- optional slot overrides -->
    </AdminDataTable>

    <!-- Create/Edit Modal -->
    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit [Module]' : 'Tambah [Module]'"
      :loading="saving"
      @close="modal = false"
      @submit="save"
    >
      <UiInput v-model="form.field" label="Field" required />
      ...
    </AdminFormModal>

    <!-- Delete Confirmation -->
    <UiModal :show="deleteModal" title="Hapus Data" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus data ini?</p>
      <div class="flex justify-end gap-3 pt-4">
        <UiButton variant="secondary" @click="deleteModal = false">Batal</UiButton>
        <UiButton variant="danger" @click="doDelete" :disabled="deleting">{{ deleting ? 'Menghapus...' : 'Hapus' }}</UiButton>
      </div>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })
// ... data fetching + CRUD logic
</script>
```

**OPD columns:** kode_opd, nama_opd, telepon, kepala_opd
**GIS Layer columns:** nama_layer, warna (color dot), is_active
**Jenis Pemanfaatan columns:** kode, nama, dasar_hukum (truncated)
**Pihak Ketiga columns:** nama, jenis, npwp, telepon
**Roles columns:** name, guard_name, users_count

---

### Task 9: Frontend — Kategori Aset CRUD with Cascading Dropdown

**Files:**
- Create: `frontend/pages/admin/kategori-aset.vue`

Special: cascading 3-level selector (KIB → sub-kategori → leaf) inside form.

The API endpoint `GET /api/kategori-aset` returns tree data. The form selects parent via cascading dropdown filtering by `kode_kib` (KIB A / KIB C only) and `is_leaf`.

- [ ] **Create halaman Kategori Aset** with cascading dropdown for selecting parent_kategori
- [ ] Create/edit form with fields: kode_kib (select A/C), kode_kategori, nama_kategori, parent (cascading), is_leaf (checkbox)

---

### Task 10: Frontend — Aset CRUD (Complex)

**Files:**
- Create: `frontend/pages/admin/aset.vue`

Complex form with:
- Dropdown OPD (from /api/opd)
- Cascading dropdown Kategori (filtered by KIB A/C from /api/kategori-aset)
- Standard fields: kode_barang, register, nama_barang, tahun_perolehan, nilai_perolehan, nilai_buku, luas, kondisi (Baik/Rusak_Ringan/Rusak_Berat), status (Aktif/Idle/Dimanfaatkan), alamat, keterangan
- Photo gallery (show existing photos, upload new)
- Search dashboard: search by nama_barang/kode_barang, filter by OPD/kategori/kondisi/status

---

### Task 11: Frontend — Pemanfaatan CRUD (Complex)

**Files:**
- Create: `frontend/pages/admin/pemanfaatan.vue`

Fields: aset_id (searchable dropdown of aset), jenis_pemanfaatan_id (select), pihak_ketiga_id (select), nomor_perjanjian, tanggal_mulai, tanggal_selesai, nilai_kontrak, kontribusi_tahunan, peruntukan, status, catatan
Document upload section (show existing docs, upload new)

---

### Task 12: Frontend — Users CRUD

**Files:**
- Create: `frontend/pages/admin/users.vue`

Fields: name, email, password (on create only), password_confirmation, role_id (select from /api/roles)

---

### Task 13: Auth Guard — Redirect if not logged in

**Files:**
- Create: `frontend/middleware/auth.ts`

```ts
// frontend/middleware/auth.ts
import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()
  if (!auth.token.value && to.path.startsWith('/admin')) {
    return navigateTo('/auth/login')
  }
  if (auth.token.value && to.path === '/auth/login') {
    return navigateTo('/admin')
  }
})
```

Register middleware in `nuxt.config.ts`:
```ts
export default defineNuxtConfig({
  // ...existing config
  router: {
    middleware: ['auth']
  }
})
```

---

### Task 14: Backend — Run Migrations & Seeders

- [ ] Run `docker-compose up -d` to start PostgreSQL
- [ ] Run `cd backend && php artisan migrate:fresh --seed` to apply all migrations + seeders
