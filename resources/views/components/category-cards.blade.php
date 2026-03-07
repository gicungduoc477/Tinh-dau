@props(['categories'])

<div class="row row-cols-2 row-cols-md-4 g-3 category-cards">
    @foreach($categories as $cat)
        @php $isActive = request('category') == $cat->slug; @endphp
        <div class="col">
            <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="text-decoration-none" @if($isActive) aria-current="true" @endif>
                <div class="card text-center h-100 {{ $isActive ? 'category-card-active' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                        @if(!empty($cat->icon) && \Illuminate\Support\Str::startsWith($cat->icon, 'bi-'))
                            <i class="bi {{ $cat->icon }} mb-2"></i>
                        @elseif(!empty($cat->icon))
                            <img src="{{ asset($cat->icon) }}" alt="{{ $cat->name }}" class="mb-2" style="width:48px;height:48px;object-fit:contain;">
                        @else
                            <i class="bi bi-list mb-2"></i>
                        @endif

                        <div class="fw-semibold text-dark small">{{ $cat->name }}</div>
                        <small class="text-muted"><span class="badge bg-light text-dark mt-2">{{ $cat->products_count ?? 0 }}</span></small>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>