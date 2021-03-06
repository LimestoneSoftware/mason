@extends('layouts.backend')

@section('content')
    <section class="section">
        <form
            class="autosave"
            action="{{ route('backend.menus.index') }}"
            method="GET"
        >
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <div>
                            <h1 class="title is-1">
                                <a href="{{ route('backend.menus.index') }}">
                                    {{ __('menus.title') }}
                                </a>
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="level-right">
                    <div class="level-item">
                        <div class="field">
                            <div class="control">
                                <div class="select">
                                    <select name="location" autocomplete="off">
                                        <option value="">{{ __('menus.attributes.location') }}</option>
                                        @foreach($menuLocations as $menuLocation)
                                            <option
                                                value="{{ $menuLocation->name }}"
                                                {{ isset($request->location) && $menuLocation->name === $request->location ? 'selected' : '' }}
                                            >{{ $menuLocation->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="level-item">
                        <div class="field">
                            <div class="control">
                                <div class="select">
                                    <select name="locale_id" autocomplete="off">
                                        <option value="">{{ __('menus.attributes.locale') }}</option>
                                        @foreach($locales as $locale)
                                            <option
                                                value="{{ $locale->id }}"
                                                {{ isset($request->locale_id) && $locale->id == $request->locale_id ? 'selected' : '' }}
                                            >{{ $locale }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <hr />

        @isset($menu)
            @if ($menu->root_items->count() > 0)
                <fieldset class="menu-items">
                    <ul>
                        @foreach($menu->root_items as $item)
                            <li>
                                @include('backend.menus.partials.item')
                            </li>
                        @endforeach
                    </ul>
                </fieldset>

                <a
                    class="button is-primary"
                    href="{{ route('backend.menus.items.create', [$menu]) }}"
                >
                    <span class="icon"><i class="fa-light fa-plus"></i></span>
                    <span>{{ __('menus.items.actions.create.label') }}</span>
                </a>
            @else
                <div class="section has-text-centered">
                    <a
                        class="button is-primary"
                        href="{{ route('backend.menus.items.create', [$menu]) }}"
                    >
                        <span class="icon"><i class="fa-light fa-plus"></i></span>
                        <span>{{ __('menus.items.actions.create.label') }}</span>
                    </a>
                </div>
            @endif
        @endisset
    </section>
@endsection
