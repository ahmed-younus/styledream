<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-sm font-medium text-[#697386]">Admin Team Management</h2>
        <button wire:click="openCreateModal" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
            + Add Admin
        </button>
    </div>

    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-[#30b566]/10 border border-[#30b566]/20 rounded-lg text-[#30b566] text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 p-4 bg-[#df1b41]/10 border border-[#df1b41]/20 rounded-lg text-[#df1b41] text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-6">
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Search admins..."
               class="w-full max-w-md px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-sm text-[#1a1f36] placeholder-[#697386] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors">
    </div>

    {{-- Admins Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($admins as $admin)
            <div class="bg-white rounded-lg border border-[#e3e8ee] p-5 hover:border-[#d0d5dd] transition-colors">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#f0f3f7] rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-[#1a1f36]">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-[#1a1f36] text-sm">{{ $admin->name }}</h3>
                            <p class="text-xs text-[#697386]">{{ $admin->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-[#635bff] text-white">
                        {{ $roles[$admin->role] ?? ucfirst($admin->role) }}
                    </span>

                    <div class="flex items-center gap-1">
                        <button wire:click="openEditModal({{ $admin->id }})"
                                class="p-2 text-[#697386] hover:text-[#1a1f36] hover:bg-[#f6f8fa] rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                        </button>

                        @if($admin->id !== auth('admin')->id())
                            <button wire:click="delete({{ $admin->id }})"
                                    wire:confirm="Are you sure you want to delete this admin?"
                                    class="p-2 text-[#697386] hover:text-[#df1b41] hover:bg-[#df1b41]/10 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-[#e3e8ee]">
                    <p class="text-xs text-[#697386]">
                        Last login: {{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-[#697386] text-sm">
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
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4 border border-[#e3e8ee]" @click.away="$wire.showModal = false">
                <div class="p-5 border-b border-[#e3e8ee]">
                    <h3 class="text-base font-semibold text-[#1a1f36]">
                        {{ $editingId ? 'Edit Admin User' : 'Create Admin User' }}
                    </h3>
                </div>

                <form wire:submit="save">
                    <div class="p-5 space-y-4">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Name</label>
                            <input wire:model="name"
                                   type="text"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors text-sm"
                                   placeholder="Full name">
                            @error('name') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Email</label>
                            <input wire:model="email"
                                   type="email"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors text-sm"
                                   placeholder="admin@example.com">
                            @error('email') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">
                                Password @if($editingId) <span class="text-[#697386]">(leave blank to keep current)</span> @endif
                            </label>
                            <input wire:model="password"
                                   type="password"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors text-sm"
                                   placeholder="{{ $editingId ? '••••••••' : 'Minimum 8 characters' }}">
                            @error('password') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Role</label>
                            <select wire:model="role"
                                    class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors text-sm">
                                @foreach($roles as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror

                            <div class="mt-2 text-xs text-[#697386] space-y-0.5">
                                <p><strong>Super Admin:</strong> Full access to everything</p>
                                <p><strong>Admin:</strong> Can manage users, settings, content</p>
                                <p><strong>Moderator:</strong> Can only moderate content</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border-t border-[#e3e8ee] flex justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-[#697386] hover:text-[#1a1f36] hover:bg-[#f6f8fa] rounded-lg transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
