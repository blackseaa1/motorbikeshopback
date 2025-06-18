@extends('customer.layouts.app')

@section('title', 'Liên hệ - MotoToys')

@section('content')
    <main class="py-5">
        <div class="container">
            <h1 class="text-center mb-4">Contact Us</h1>
            <p class="text-center lead mb-5">We'd love to hear from you! Please fill out the form below or use the contact
                details provided.</p>

            <div class="row">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Send us a Message</h4>
                            <form>
                                <div class="mb-3">
                                    <label for="contactName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="contactName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="contactEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactSubject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="contactSubject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Message</label>
                                    <textarea class="form-control" id="contactMessage" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Contact Information</h4>
                            <ul class="list-unstyled">
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-geo-alt-fill fs-4 me-3"></i>
                                    <span>123 Auto Parts St, Car City, 12345</span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-telephone-fill fs-4 me-3"></i>
                                    <span>(123) 456-7890</span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-envelope-fill fs-4 me-3"></i>
                                    <span>contact@shopeaseauto.com</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Our Location</h4>
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.21949339347!2d-73.98785368459385!3d40.75807997932693!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6452555%3A0x8c358f232434524!2sTimes%20Square!5e0!3m2!1sen!2sus!4v1616428732959!5m2!1sen!2sus"
                                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection