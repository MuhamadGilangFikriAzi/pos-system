@extends("layouts.app")
@section("title", isset($supplier) ? "Edit Supplier" : "Tambah Supplier")
@section("content")
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-lg mb-6">{{ isset($supplier) ? '✏️ Edit Supplier' : '🏢 Tambah Supplier' }}</h3>
        <form method="POST" action="{{ isset($supplier) ? route('inventory.suppliers.update', $supplier) : route('inventory.suppliers.store') }}" class="space-y-4">
            @csrf @if(isset($supplier)) @method('PUT') @endif
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label><input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Kode (biarkan kosong untuk auto)</label><input type="text" name="code" value="{{ old('code', $supplier->code ?? '') }}" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Kontak Person</label><input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label><input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label><textarea name="address" rows="2" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm">{{ old('address', $supplier->address ?? '') }}</textarea></div>
            <div class="flex items-center gap-2"><input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4"><label for="is_active" class="text-sm">Aktif</label></div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition">{{ isset($supplier) ? 'Simpan' : 'Tambah' }}</button>
                <a href="{{ route('inventory.suppliers.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
