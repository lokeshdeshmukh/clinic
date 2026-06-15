<?php $isEdit = is_array($doctor); ?>
<div class="mx-auto max-w-3xl panel">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.22em] text-slate-500">Doctor profile</p>
        <h1 class="text-3xl font-semibold"><?= $isEdit ? 'Edit doctor' : 'Add doctor' ?></h1>
    </div>
    <form method="post" action="<?= e(url($isEdit ? '/admin/doctors/' . $doctor['id'] . '/update' : '/admin/doctors')) ?>" enctype="multipart/form-data" class="grid gap-4">
        <?= csrf_field() ?>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="name">Doctor name</label>
                <input id="name" name="name" value="<?= e((string) ($doctor['name'] ?? old('name'))) ?>" required>
            </div>
            <div>
                <label for="specialization">Specialization</label>
                <input id="specialization" name="specialization" value="<?= e((string) ($doctor['specialization'] ?? old('specialization'))) ?>" required>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="consultation_fee">Consultation fee</label>
                <input id="consultation_fee" type="number" step="0.01" name="consultation_fee" value="<?= e((string) ($doctor['consultation_fee'] ?? old('consultation_fee', '0'))) ?>" required>
            </div>
            <div>
                <label for="slot_duration_minutes">Slot duration</label>
                <input id="slot_duration_minutes" type="number" name="slot_duration_minutes" value="<?= e((string) ($doctor['slot_duration_minutes'] ?? old('slot_duration_minutes', config('app.default_slot_duration', 30)))) ?>" required>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= selected($doctor['status'] ?? old('status', 'active'), 'active') ?>>Active</option>
                    <option value="inactive" <?= selected($doctor['status'] ?? old('status', 'active'), 'inactive') ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div>
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"><?= e((string) ($doctor['bio'] ?? old('bio'))) ?></textarea>
        </div>
        <div>
            <label for="profile_photo">Profile photo</label>
            <input id="profile_photo" type="file" name="profile_photo" accept="image/*">
        </div>
        <div class="flex flex-wrap gap-3">
            <button class="btn-primary"><?= $isEdit ? 'Save changes' : 'Create doctor' ?></button>
            <a href="<?= e(url('/admin/doctors')) ?>" class="btn-secondary">Back</a>
        </div>
    </form>
</div>
