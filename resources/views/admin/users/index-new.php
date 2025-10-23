@extends('layouts.admin-new')

@section('title', 'Users Management')

@section('menu-users', 'active')

@section('content')
<div class="block-header">
    <div class="row clearfix">
        <div class="col-lg-5 col-md-5 col-sm-12">
            <h2 class="float-left">Users</h2>
            <a href="<?php echo url('/admin/users/create'); ?>">
                <button class="new-widget-btn btn btn-primary btn-sm float-left">
                    <i class="zmdi zmdi-plus"></i> New User
                </button>
            </a>
            <a href="<?php echo url('/admin/users/invite'); ?>">
                <button class="new-widget-btn btn btn-primary btn-sm float-left">
                    <i class="zmdi zmdi-mail-send"></i> Invite
                </button>
            </a>
        </div>
    </div>
</div>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12">

        <div class="card">
            <div class="body">
                <form method="GET" action="<?php echo url('/admin/users'); ?>" accept-charset="UTF-8" id="filter-form" class="input-group">
                    <input class="form-control filter-text" name="q" value="<?php echo htmlspecialchars($search ?? ''); ?>"
                           placeholder="Enter text to search by name or email">
                    <div class="input-group-append">
                        <button type="submit" name="filter" value="1"
                                class="btn btn-primary btn-round waves-effect filter-submit-btn">
                            SEARCH
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="body table-responsive">
                <table class="table m-b-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>HOTEL</th>
                        <th>MAIL</th>
                        <th>RESELLER</th>
                        <th>JOINED AT</th>
                        <th>ACTIONS</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="py-4">
                                        <i class="zmdi zmdi-account-o zmdi-hc-2x text-muted mb-2"></i>
                                        <p class="text-muted">No users found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <th scope="row"><?php echo htmlspecialchars($user['id']); ?></th>
                                    <td class="td-namesurname">
                                        <a href="<?php echo url('/admin/users/edit/' . $user['id']); ?>">
                                            <?php echo htmlspecialchars($user['namesurname']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if($user['hotel_name']): ?>
                                            <?php if($user['hotel_web_url']): ?>
                                                <a href="<?php echo htmlspecialchars($user['hotel_web_url']); ?>" target="_blank" class="hotel-link">
                                                    <?php echo htmlspecialchars($user['hotel_name']); ?>
                                                    <i class="zmdi zmdi-open-in-new" style="font-size: 12px; margin-left: 4px;"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="hotel-name"><?php echo htmlspecialchars($user['hotel_name']); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No hotel</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="text-center">
                                        <?php if($user['is_admin']): ?>
                                            <span class="badge badge-danger">ADMIN</span>
                                        <?php elseif($user['user_type'] == 2): ?>
                                            <span class="badge badge-warning">RESELLER</span>
                                        <?php elseif($user['reseller_id'] > 0): ?>
                                            <span class="badge badge-info">CUSTOMER</span>
                                            <?php if($user['reseller_name']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($user['reseller_name']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">STANDARD</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="text-nowrap text-right">
                                        <a href="<?php echo url('/admin/users/switch/' . $user['id'] . '?redirect=hotels'); ?>" title="EDIT PROPERTY">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-city-alt"></i></button>
                                        </a>
                                        <a href="<?php echo url('/admin/users/delete/' . $user['id']); ?>" onclick="return confirm('Are you sure you want to delete this user?')" title="DELETE USER">
                                            <button class="btn btn-danger btn-sm"><i class="zmdi zmdi-delete"></i></button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if(isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <!-- First Button -->
                            <?php if($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo url('/admin/users?page=1' . (isset($search) && $search ? '&q=' . urlencode($search) : '')); ?>" title="First Page">
                                        <i class="zmdi zmdi-skip-previous"></i> First
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="zmdi zmdi-skip-previous"></i> First</span>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Previous Button -->
                            <?php if($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo url('/admin/users?page=' . $pagination['prev_page'] . (isset($search) && $search ? '&q=' . urlencode($search) : '')); ?>" title="Previous Page">
                                        <i class="zmdi zmdi-chevron-left"></i> Prev
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="zmdi zmdi-chevron-left"></i> Prev</span>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Page Numbers -->
                            <?php 
                            $startPage = max(1, $pagination['current_page'] - 2);
                            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            ?>
                            
                            <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if($i == $pagination['current_page']): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?php echo $i; ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo url('/admin/users?page=' . $i . (isset($search) && $search ? '&q=' . urlencode($search) : '')); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Next Button -->
                            <?php if($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo url('/admin/users?page=' . $pagination['next_page'] . (isset($search) && $search ? '&q=' . urlencode($search) : '')); ?>" title="Next Page">
                                        Next <i class="zmdi zmdi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next <i class="zmdi zmdi-chevron-right"></i></span>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Last Button -->
                            <?php if($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo url('/admin/users?page=' . $pagination['total_pages'] . (isset($search) && $search ? '&q=' . urlencode($search) : '')); ?>" title="Last Page">
                                        Last <i class="zmdi zmdi-skip-next"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Last <i class="zmdi zmdi-skip-next"></i></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <!-- Go to Page Input -->
                        <div class="pagination-goto mt-3 text-center">
                            <form method="GET" action="<?php echo url('/admin/users'); ?>" class="d-inline-flex align-items-center">
                                <?php if(isset($search) && $search): ?>
                                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                <span class="mr-2">Go to page:</span>
                                <input type="number" name="page" min="1" max="<?php echo $pagination['total_pages']; ?>" 
                                       value="<?php echo $pagination['current_page']; ?>" 
                                       class="form-control form-control-sm pagination-input mr-2" 
                                       style="width: 80px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
                                <span class="ml-2 text-muted">of <?php echo $pagination['total_pages']; ?></span>
                            </form>
                        </div>
                        
                        <!-- Pagination Info -->
                        <div class="pagination-info text-center mt-3">
                            <small class="text-muted">
                                Showing <?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?> 
                                to <?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total']); ?> 
                                of <?php echo $pagination['total']; ?> results
                            </small>
                        </div>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .new-widget-btn {
        margin-left: 10px;
    }
    .hotel-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }
    .hotel-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }
    .hotel-name {
        color: #333;
        font-weight: 500;
    }
    
    /* Pagination styles */
    .pagination-goto {
        margin: 15px 0;
    }
    
    .pagination-input {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        text-align: center;
        font-size: 14px;
    }
    
    .pagination-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        outline: none;
    }
    
    .d-inline-flex {
        display: inline-flex;
    }
    
    .align-items-center {
        align-items: center;
    }
    
    .btn-outline-primary {
        color: #007bff;
        border-color: #007bff;
        background-color: transparent;
    }
    
    .btn-outline-primary:hover {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
</style>
@endsection
