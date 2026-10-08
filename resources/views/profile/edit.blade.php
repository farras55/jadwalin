<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#2D2A3E] tracking-tight">Pengaturan Profil Akun</h1>
                <p class="text-sm text-[#7C7896] mt-0.5">Kelola identitas profil, nomor kontak, serta keamanan akun Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Status: <span class="capitalize">{{ $user->status ?? 'Active' }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        {{-- ===== USER IDENTITY OVERVIEW CARD ===== --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-6 shadow-sm">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="h-16 w-16 md:h-20 md:w-20 rounded-2xl object-cover ring-2 ring-[#E2E0F7] shadow-sm shrink-0">
                    @else
                        <div class="h-16 w-16 md:h-20 md:w-20 rounded-2xl bg-gradient-to-br from-[#6C5CE7] to-[#4F46E5] text-white flex items-center justify-center font-bold text-2xl md:text-3xl ring-2 ring-[#E2E0F7] shadow-sm shrink-0">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-[#2D2A3E]">{{ $user->name }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->role === 'superadmin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'manager' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        <p class="text-sm text-[#7C7896] mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            {{ $user->email }}
                        </p>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-[#7C7896]">
                            <span class="flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                {{ $user->company->name ?? 'Jadwalin System' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                {{ $user->department->name ?? 'General' }} &bull; {{ $user->position->name ?? 'Staff' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 self-stretch md:self-auto border-t md:border-t-0 pt-4 md:pt-0 border-[#E2E0F7]">
                    <div class="bg-[#F5F3FF] border border-[#E2E0F7] rounded-xl px-4 py-2.5 text-center flex-1 md:flex-initial">
                        <span class="block text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider">Tipe Karyawan</span>
                        <span class="text-xs font-bold text-[#2D2A3E] capitalize">{{ str_replace('_', ' ', $user->employment_type ?? 'full_time') }}</span>
                    </div>
                    <div class="bg-[#F5F3FF] border border-[#E2E0F7] rounded-xl px-4 py-2.5 text-center flex-1 md:flex-initial">
                        <span class="block text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider">Bergabung Sejak</span>
                        <span class="text-xs font-bold text-[#2D2A3E]">{{ $user->join_date ? \Carbon\Carbon::parse($user->join_date)->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== FORMS SECTION ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left / Main Column: Profile Information Form (Spans 2 columns) --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 bg-white rounded-2xl border border-[#E2E0F7] shadow-sm">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Right Column: Security & Danger Zone --}}
            <div class="space-y-6">
                {{-- Update Password --}}
                <div class="p-6 bg-white rounded-2xl border border-[#E2E0F7] shadow-sm">
                    @include('profile.partials.update-password-form')
                </div>

                {{-- Delete Account --}}
                <div class="p-6 bg-white rounded-2xl border border-red-200 bg-red-50/20 shadow-sm">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>