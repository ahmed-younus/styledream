<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400">Admin Team Management</h2>
        <button wire:click="openCreateModal" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
            + Add Admin
        </button>
    </div>

    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-6">
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Search admins..."
               class="w-full max-w-md px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    {{-- Admins Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($admins as $admin)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                            <span class="text-lg font-bold text-white">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $admin->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $admin->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 text-xs font-medium rounded-full
                        @if($admin->role === 'super_admin') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                        @elseif($admin->role === 'admin') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                        @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                        @endif">
                        {{ $roles[$admin->role] ?? ucfirst($admin->role) }}
                    </span>

                    <div class="flex items-center gap-2">
                        <button wire:click="openEditModal({{ $admin->id }})"
                                class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>

                        @if($admin->id !== auth('admin')->id())
                            <button wire:click="delete({{ $admin->id }})"
                                    wire:confirm="Are you sure you want to delete this admin?"
                                    class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Last login: {{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                No admin users found.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $admins->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-4" @click.away="$wire.showModal = false">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $editingId ? 'Edit Admin User' : 'Create Admin User' }}
                    </h3>
                </div>

                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input wire:model="name"
                                   type="text"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="Full name">
                            @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input wire:model="email"
                                   type="email"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="admin@example.com">
                            @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Password @if($editingId) <span class="text-gray-500">(leave blank to keep current)</span> @endif
                            </label>
                            <input wire:model="password"
                                   type="password"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="{{ $editingId ? '••••••••' : 'Minimum 8 characters' }}">
                            @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select wire:model="role"
                                    class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                                @foreach($roles as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <p><strong>Super Admin:</strong> Full access to everything</p>
                                <p><strong>Admin:</strong> Can manage users, settings, content</p>
                                <p><strong>Moderator:</strong> Can only moderate content</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
