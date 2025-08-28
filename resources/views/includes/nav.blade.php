<nav class="navbar navbar-expand-lg nav">
    <div class="container-fluid">

        <div class="d-flex">
            <a class="my_btn_1 mylinknavs" href="{{ route('admin.dashboard') }}">Dashboard</a>
        </div>

        <button class="navbar-toggler myitem" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse flex-grow-0 me-5" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2">                
                <li class="nav-item dropdown">
                    <a class="nav-link my_btn_7" href="{{ route('admin.profile.edit') }}">
                        {{auth()->user()->name}}
                    </a>                 
                </li>   
            </ul>   
        </div>
    </div>
  </nav>



  