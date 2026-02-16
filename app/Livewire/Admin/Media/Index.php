<?php

namespace App\Livewire\Admin\Media;

use App\Models\Media as MediaModel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

#[Layout('layouts.app')]
#[Title('Media')]
class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithoutUrlPagination;

    public string $search = '';

    public $uploadedFile = null;

    public string $altText = '';

    public bool $showUploadModal = false;

    public ?int $previewId = null;

    public int $perPage = 24;

    protected array $perPageOptions = [12, 24, 48, 96];

    public bool $showPreviewModal = false;

    protected function rules(): array
    {
        return [
            'uploadedFile' => ['required', 'file', 'max:10240'], // 10MB
            'altText' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read media'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openUpload(): void
    {
        abort_unless(auth()->user()?->can('create media'), 403);
        $this->reset(['uploadedFile', 'altText']);
        $this->showUploadModal = true;
    }

    public function saveUpload(): void
    {
        abort_unless(auth()->user()?->can('create media'), 403);
        $this->validate();
        $file = $this->uploadedFile;
        $path = $file->store('media/'.date('Y/m'), 'public');
        MediaModel::create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $this->altText ?: null,
            'uploaded_by' => auth()->id(),
        ]);
        session()->flash('status', __('File uploaded successfully.'));
        $this->showUploadModal = false;
        $this->reset(['uploadedFile', 'altText']);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete media'), 403);
        $media = MediaModel::findOrFail($id);
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
        if ($this->previewId === $id) {
            $this->closePreview();
        }
        session()->flash('status', __('File deleted successfully.'));
    }

    public function preview(int $id): void
    {
        $this->previewId = $id;
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->previewId = null;
        $this->showPreviewModal = false;
    }

    public function render(): View
    {
        $media = MediaModel::query()
            ->select(['id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'created_at'])
            ->when($this->search, function (Builder $q): void {
                $q->where('original_name', 'like', '%'.$this->search.'%')
                    ->orWhere('alt_text', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.media.index', [
            'media' => $media,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
