<nav class="navbar navbar-expand-lg nav">
    <div class="container-fluid">

        <div class="d-flex">
            <a class="my_btn_1 mylinknavs" href="{{ route('client.dashboard') }}">Dashboard</a>
        </div>

        <button class="navbar-toggler myitem" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse flex-grow-0 me-5" id="navbarNavDropdown">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2">

                
                <li class="nav-item dropdown opacity-50">
                    <a class="nav-link mylinknav dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Fatture
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.posts.index') }}">Mostra tutti</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.posts.create') }}">Aggiungi</a></li>
                    </ul>
                </li>

                {{-- <li class="nav-item ">
                    <a class="nav-link mylinknav" href="{{ route('admin.dates.index') }}">
                        Gestione servizio
                    </a>
                </li>    --}}
                  
                <li class="nav-item dropdown">
                    <a class="nav-link my_btn_7" href="{{ route('client.profile.edit') }}">
                        {{auth()->user()->name}}
                    </a>                 
                </li>   
                {{-- <li class="nav-item " >
                    <button id="theme-toggle" class="my_btn_1 h-100">
                        <svg id="dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16">
                            <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
                        </svg>

                        <svg id="light" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16">
                            <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                        </svg>
                    </button>
                </li> --}}
                

            </ul>   
        </div>
    </div>
  </nav>



  