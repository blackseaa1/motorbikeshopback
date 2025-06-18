@extends('customer.layouts.app')

@section('title', 'Blog - MotoToys')

@section('content')
<main class="py-5">
  <div class="container">
    <h1 class="mb-4">Our Blog</h1>
    <p class="lead mb-5">Latest news, tips, and insights from the world of auto parts.</p>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="Blog Post Image">
          <div class="card-body">
            <h2 class="card-title">The Ultimate Guide to Choosing the Right Brakes</h2>
            <p class="text-muted">Posted on January 1, 2023 by Admin</p>
            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua...</p>
            <a href="#" class="btn btn-primary">Read More &rarr;</a>
          </div>
        </div>

        <div class="card mb-4">
            <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="Blog Post Image">
          <div class="card-body">
            <h2 class="card-title">5 Essential Maintenance Tips for Your Vehicle</h2>
             <p class="text-muted">Posted on February 15, 2023 by Admin</p>
            <p class="card-text">Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat...</p>
            <a href="#" class="btn btn-primary">Read More &rarr;</a>
          </div>
        </div>

        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
          </ul>
        </nav>
      </div>

      <div class="col-lg-4">
        <div class="card mb-4">
          <h5 class="card-header">Search</h5>
          <div class="card-body">
            <div class="input-group">
              <input type="text" class="form-control" placeholder="Search for...">
              <button class="btn btn-secondary" type="button">Go!</button>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <h5 class="card-header">Categories</h5>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-6">
                <ul class="list-unstyled mb-0">
                  <li><a href="#">Brakes</a></li>
                  <li><a href="#">Engine Parts</a></li>
                  <li><a href="#">Suspension</a></li>
                </ul>
              </div>
              <div class="col-lg-6">
                <ul class="list-unstyled mb-0">
                  <li><a href="#">Filters</a></li>
                  <li><a href="#">Lighting</a></li>
                  <li><a href="#">Body Parts</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection