@extends('layouts.app')

@section('title', 'Beranda')
@section('full_width', true)

@section('content')
@php
    $allFeaturedPosts = collect($popularPosts ?? [])->merge($newestPosts ?? [])->unique('id')->values();

    $imageFor = function ($post, $size = 'w1200') {
        if (!$post) return null;

        if (!empty($post['attachments'])) {
            foreach ($post['attachments'] as $file) {
                if (str_contains($file['mime'] ?? '', 'image')) {
                    $rawUrl = $file['file_url'] ?? ('public/uploads/' . ($file['filename'] ?? ''));
                    $cleanPath = str_replace('\\', '/', $rawUrl);
                    return str_starts_with($cleanPath, 'http')
                        ? $cleanPath
                        : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($cleanPath, '/');
                }
            }
        }

        if (!empty($post['gdrive_folder_items'])) {
            foreach ($post['gdrive_folder_items'] as $item) {
                if (str_contains($item['mimeType'] ?? '', 'image') && !empty($item['thumbnailLink'])) {
                    return str_replace('=s220', '=' . $size, $item['thumbnailLink']);
                }
            }
        }

        if (!empty($post['gdrive_url'])) {
            if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $post['gdrive_url'], $m)) {
                return 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=' . $size;
            }
            if (preg_match('/id=([a-zA-Z0-9_-]+)/', $post['gdrive_url'], $m)) {
                return 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=' . $size;
            }
        }

        return null;
    };

    $categories = [
        ['label' => 'Semua', 'href' => route('home')],
        ['label' => 'Populer', 'href' => route('home', ['sort' => 'popular'])],
        ['label' => 'Terbaru', 'href' => route('home', ['sort' => 'newest'])],
        ['label' => 'Magang', 'href' => route('home', ['category' => 'kp/magang'])],
        ['label' => 'Penelitian', 'href' => route('home', ['category' => 'penelitian/pkm'])],
        ['label' => 'Lomba', 'href' => route('home', ['category' => 'lomba'])],
        ['label' => 'Proyek', 'href' => route('home', ['category' => 'project mandiri'])],
        ['label' => 'Skripsi', 'href' => route('home', ['category' => 'skripsi'])],
    ];

    $heroSlides = collect([
        [
            'label' => 'Terbaru',
            'headline' => 'Baru Dipublikasikan',
            'description' => 'Karya terbaru yang baru masuk ke etalase mahasiswa UMDP.',
            'post' => collect($newestPosts ?? [])->first(),
        ],
        [
            'label' => 'Banyak Like',
            'headline' => 'Paling Disukai',
            'description' => 'Karya dengan dukungan like paling tinggi dari pengunjung.',
            'post' => $allFeaturedPosts->sortByDesc(fn ($post) => (int) ($post['likeCount'] ?? 0))->first(),
        ],
        [
            'label' => 'Banyak Komentar',
            'headline' => 'Ramai Diskusi',
            'description' => 'Karya yang paling banyak mendapatkan komentar dan percakapan.',
            'post' => $allFeaturedPosts->sortByDesc(fn ($post) => (int) ($post['commentCount'] ?? 0))->first(),
        ],
    ])->filter(fn ($slide) => !empty($slide['post']))->values();

    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([[
            'label' => 'Karya Pilihan',
            'headline' => 'Karya Mahasiswa UMDP',
            'description' => 'Temukan inovasi, riset, proyek kreatif, dan portofolio digital mahasiswa Universitas Multi Data Palembang.',
            'post' => null,
        ]]);
    }
    $firstHeroPost = $heroSlides->first()['post'] ?? null;
@endphp

<section class="relative min-h-[680px] overflow-hidden bg-white" data-hero-carousel>
    <div class="flex min-h-[680px] scroll-smooth transition-transform duration-700 ease-out" data-hero-track>
        @foreach($heroSlides as $slide)
            @php
                $heroPost = $slide['post'];
                $heroImage = $imageFor($heroPost, 'w1600');
            @endphp
            <article class="relative min-h-[680px] min-w-full overflow-hidden bg-white">
                @if($heroImage)
                    <img src="{{ $heroImage }}" alt="{{ $heroPost['title'] ?? 'Karya Mahasiswa' }}" class="absolute inset-0 h-full w-full object-cover object-center">
                    <div class="absolute inset-0 bg-gradient-to-r from-white via-white/92 to-white/20"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-white via-white/25 to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,#fff_0%,#f8fafc_45%,#fee2e2_100%)]"></div>
                @endif

                <div class="relative mx-auto flex min-h-[500px] max-w-7xl items-center px-4 py-16 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        <div class="mb-5 inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-xs font-black uppercase tracking-wide text-white shadow-lg shadow-red-600/20">
                            {{ $slide['label'] }}
                        </div>

                        <p class="mb-3 text-sm font-semibold italic text-slate-500">{{ $slide['description'] }}</p>

                        <h1 class="mb-4 text-4xl font-black leading-tight text-slate-950 sm:text-6xl">
                            {{ $heroPost['title'] ?? $slide['headline'] }}
                        </h1>

                        <div class="mb-5 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1 text-amber-500">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M10 1.5l2.47 5.32 5.82.7-4.3 3.98 1.14 5.75L10 14.38l-5.13 2.87 1.14-5.75-4.3-3.98 5.82-.7L10 1.5z"/></svg>
                                {{ $heroPost['likeCount'] ?? 0 }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-slate-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $heroPost['commentCount'] ?? 0 }}
                            </span>
                            <span>{{ isset($heroPost['created_at']) ? \Carbon\Carbon::parse($heroPost['created_at'])->format('Y') : now()->format('Y') }}</span>
                            <span>{{ $heroPost['category'] ?? 'Karya Mahasiswa' }}</span>
                            <span>{{ $heroPost['author']['full_name'] ?? 'Universitas Multi Data Palembang' }}</span>
                        </div>

                        <p class="max-w-xl text-base leading-8 text-slate-600 sm:text-lg">
                            {{ \Illuminate\Support\Str::limit(strip_tags($heroPost['caption'] ?? 'Temukan inovasi, riset, proyek kreatif, dan portofolio digital mahasiswa Universitas Multi Data Palembang.'), 210) }}
                        </p>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pointer-events-none absolute inset-x-0 top-[440px] z-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="pointer-events-auto flex max-w-3xl flex-wrap items-center gap-3">
                @if($firstHeroPost)
                    <a href="{{ route('posts.show', $firstHeroPost['id']) }}" data-hero-action class="inline-flex items-center gap-3 rounded-lg bg-red-600 px-6 py-3 text-base font-black text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M5 3l14 9-14 9V3z"/></svg>
                        Lihat Karya
                    </a>
                @endif
                <form action="{{ route('home') }}" method="GET" class="flex min-w-0 flex-1 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm sm:max-w-sm">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari karya..." class="min-w-0 flex-1 px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400">
                    <button class="bg-slate-950 px-4 font-bold text-white transition hover:bg-slate-800" type="submit">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.15a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="pointer-events-none absolute inset-x-0 top-[540px] z-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="pointer-events-auto flex max-w-3xl gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach($categories as $category)
                    <a href="{{ $category['href'] }}" class="shrink-0 rounded-full border border-slate-200 bg-white/85 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm backdrop-blur transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                        {{ $category['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($heroSlides->count() > 1)
        <div class="absolute bottom-9 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 rounded-full border border-slate-200 bg-white/90 p-1 shadow-lg backdrop-blur">
            @foreach($heroSlides as $slide)
                <button type="button" data-hero-jump="{{ $loop->index }}" data-hero-url="{{ !empty($slide['post']) ? route('posts.show', $slide['post']['id']) : '' }}" class="rounded-full px-4 py-2 text-xs font-black text-slate-500 transition hover:text-red-600 {{ $loop->first ? 'bg-slate-950 text-white hover:text-white' : '' }}">
                    {{ $slide['label'] }}
                </button>
            @endforeach
        </div>
    @endif
</section>

<div class="mx-auto max-w-7xl space-y-12 px-4 pb-16 sm:px-6 lg:px-8">
    <section>
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 class="text-2xl font-black text-slate-950">Trending Now</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('home', ['sort' => 'popular']) }}" class="hidden text-sm font-black text-red-600 hover:text-red-700 sm:inline-flex">Lihat Semua</a>
                <div class="hidden rounded-full border border-slate-200 bg-slate-100 p-1 md:flex">
                    <a href="{{ route('home') }}" class="rounded-full bg-white px-5 py-2 text-sm font-black text-slate-950 shadow-sm">All</a>
                    <a href="{{ route('home', ['sort' => 'popular']) }}" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-950">Populer</a>
                    <a href="{{ route('home', ['sort' => 'newest']) }}" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-950">Terbaru</a>
                </div>
                <button type="button" data-scroll-target="trending-row" data-scroll-dir="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" aria-label="Scroll trending ke kiri">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" data-scroll-target="trending-row" data-scroll-dir="1" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" aria-label="Scroll trending ke kanan">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        @if(!empty($popularPosts) && count($popularPosts) > 0)
            <div id="trending-row" data-auto-row class="-mx-4 flex scroll-smooth gap-4 overflow-x-auto px-4 pb-3 scrollbar-none sm:mx-0 sm:px-0">
                @foreach($popularPosts as $post)
                    <x-post-card :post="$post" variant="poster" :rank="$loop->iteration" />
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                <h3 class="font-black text-slate-800">Belum ada karya populer</h3>
                <p class="mt-1 text-sm text-slate-500">Karya yang paling banyak mendapat interaksi akan tampil di sini.</p>
            </div>
        @endif
    </section>

    <section>
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 class="text-2xl font-black text-slate-950">New Release</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('home', ['sort' => 'newest']) }}" class="hidden text-sm font-black text-red-600 hover:text-red-700 sm:inline-flex">Lihat Semua</a>
                <button type="button" data-scroll-target="new-release-row" data-scroll-dir="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" aria-label="Scroll new release ke kiri">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" data-scroll-target="new-release-row" data-scroll-dir="1" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" aria-label="Scroll new release ke kanan">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        @if(!empty($newestPosts) && count($newestPosts) > 0)
            <div id="new-release-row" data-auto-row class="-mx-4 flex scroll-smooth gap-4 overflow-x-auto px-4 pb-3 scrollbar-none sm:mx-0 sm:px-0">
                @foreach($newestPosts as $post)
                    <x-post-card :post="$post" variant="poster" :rank="$loop->iteration" />
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                <h3 class="font-black text-slate-800">Belum ada karya terbaru</h3>
                <p class="mt-1 text-sm text-slate-500">Karya terbaru yang sudah dipublikasikan akan muncul di sini.</p>
            </div>
        @endif
    </section>

    @if(!session('user'))
        <section class="rounded-xl border border-slate-200 bg-slate-950 px-6 py-10 text-center shadow-xl">
            <h2 class="text-2xl font-black text-white">Punya karya untuk dipublikasikan?</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-300">Masuk dan bangun portofolio digital mahasiswa UMDP dengan tampilan yang siap dibagikan.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('login') }}" class="rounded-lg bg-red-600 px-5 py-3 text-sm font-black text-white hover:bg-red-700">Login & Upload</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-white px-5 py-3 text-sm font-black text-slate-950 hover:bg-slate-100">Daftar Gratis</a>
            </div>
        </section>
    @endif
</div>

<style>
    .scrollbar-none { scrollbar-width: none; -ms-overflow-style: none; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
</style>
<script>
    const heroCarousel = document.querySelector('[data-hero-carousel]');
    if (heroCarousel) {
        const track = heroCarousel.querySelector('[data-hero-track]');
        const buttons = [...heroCarousel.querySelectorAll('[data-hero-jump]')];
        const actionLink = heroCarousel.querySelector('[data-hero-action]');
        let heroIndex = 0;
        const heroTotal = track ? track.children.length : 0;

        const setHero = (index) => {
            if (!track || heroTotal <= 0) return;
            heroIndex = (index + heroTotal) % heroTotal;
            track.style.transform = `translateX(-${heroIndex * 100}%)`;

            buttons.forEach((button, buttonIndex) => {
                const active = buttonIndex === heroIndex;
                button.classList.toggle('bg-slate-950', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('hover:text-white', active);
                button.classList.toggle('text-slate-500', !active);
            });

            if (actionLink && buttons[heroIndex]) {
                const nextUrl = buttons[heroIndex].dataset.heroUrl;
                if (nextUrl) {
                    actionLink.href = nextUrl;
                    actionLink.classList.remove('hidden');
                } else {
                    actionLink.classList.add('hidden');
                }
            }
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => setHero(Number(button.dataset.heroJump || 0)));
        });

        if (heroTotal > 1) {
            setInterval(() => setHero(heroIndex + 1), 5200);
        }
    }

    document.querySelectorAll('[data-scroll-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.dataset.scrollTarget);
            if (!target) return;

            const direction = Number(button.dataset.scrollDir || 1);
            const amount = Math.max(260, Math.floor(target.clientWidth * 0.85));
            const maxScroll = target.scrollWidth - target.clientWidth;
            const nextLeft = target.scrollLeft + (direction * amount);

            if (nextLeft >= maxScroll - 8) {
                target.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }

            if (nextLeft <= 0) {
                target.scrollTo({ left: maxScroll, behavior: 'smooth' });
                return;
            }

            target.scrollTo({ left: nextLeft, behavior: 'smooth' });
        });
    });

    document.querySelectorAll('[data-auto-row]').forEach((row) => {
        if (row.scrollWidth <= row.clientWidth) return;

        setInterval(() => {
            const maxScroll = row.scrollWidth - row.clientWidth;
            const amount = Math.max(220, Math.floor(row.clientWidth * 0.55));
            const nextLeft = row.scrollLeft + amount;

            if (nextLeft >= maxScroll - 8) {
                row.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }

            row.scrollTo({ left: nextLeft, behavior: 'smooth' });
        }, 4200);
    });
</script>
@endsection
