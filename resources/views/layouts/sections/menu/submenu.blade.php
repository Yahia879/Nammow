<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)

    {{-- active menu method --}}
    @php
      $isSuperAdmin = auth()->user()->hasRole('super_admin');
      $isClient = auth()->user()->hasRole('client');
      $isCompany = auth()->user()->hasRole('company');
      $isEmployee = auth()->user()->hasRole('employee');

      // HARD WHITELIST FOR CLIENT ROLE
      if ($isClient) {
        $showItem = isset($submenu->slug) && str_starts_with($submenu->slug, 'client.');
      }
      // HARD WHITELIST FOR COMPANY MANAGER ROLE
      elseif ($isCompany) {
        $allowedCompanySlugs = ['structure-employees'];
        $showItem = isset($submenu->slug) && in_array($submenu->slug, $allowedCompanySlugs);
      }
      // HARD WHITELIST FOR EMPLOYEE ROLE
      elseif ($isEmployee) {
        $allowedEmployeeSlugs = ['employee-dashboard', 'employee-leaves'];
        $showItem = isset($submenu->slug) && in_array($submenu->slug, $allowedEmployeeSlugs);
      }
      // HARD WHITELIST FOR SUPER ADMIN (if manifest exists)
      elseif ($isSuperAdmin && file_exists(public_path('mix-manifest.json'))) {
        $allowedSuperAdminSlugs = ['super-admin.dashboard', 'super-admin-clients'];
        $showItem = isset($submenu->slug) && in_array($submenu->slug, $allowedSuperAdminSlugs);
      }
      // DEFAULT LOGIC FOR OTHER ROLES
      else {
        $showItem = !isset($submenu->action) || auth()->user()->canAction($submenu->action);
      }

      $activeClass = null;
      $active = $configData["layout"] === 'vertical' ? 'active open':'active';
      $currentRouteName =  Route::currentRouteName();

      if ($currentRouteName === $submenu->slug) {
          $activeClass = 'active';
      }
      elseif (isset($submenu->submenu)) {
        if (gettype($submenu->slug) === 'array') {
          foreach($submenu->slug as $slug){
            if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
                $activeClass = $active;
            }
          }
        }
        else{
          if (str_contains($currentRouteName,$submenu->slug) and strpos($currentRouteName,$submenu->slug) === 0) {
            $activeClass = $active;
          }
        }
      }
    @endphp

    @if ($showItem)
      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
          <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu])
        @endif
      </li>
    @endif
    @endforeach
  @endif
</ul>
