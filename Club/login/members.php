<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management System</title>
    <link rel="stylesheet" href="/var/www/html/Club/assets/css/style.css"> <!-- Link to Squadfree CSS -->
    <script src="/var/www/html/Club/js/main.js" defer></script> <!-- Link to Squadfree JS -->
</head>
<body>
    <!-- Navbar -->
    <header id="header" class="fixed-top d-flex align-items-center">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo">
                <h1 class="text-light"><a href="index.html"><span>Club Management</span></a></h1>
            </div>
            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link scrollto active" href="?update_type=events">Events</a></li>
                    <li><a class="nav-link scrollto" href="?update_type=recruitments">Recruitments</a></li>
                    <li><a class="nav-link scrollto" href="?update_type=applications">Applications</a></li>
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav><!-- .navbar -->
        </div>
    </header>

    <main id="main" class="main-page">
        <section class="inner-page">
            <div class="container">
                <h2>Club Management System</h2>
                <form method="post" class="form-inline">
                    <div class="form-group">
                        <label for="branch_id">Select Branch:</label>
                        <select name="branch_id" id="branch_id" class="form-control">
                            <option value="">Select Branch</option>
                            <?php while ($branch = $branchesResult->fetch_assoc()) { ?>
                                <option value="<?php echo $branch['id']; ?>" <?php if ($selectedBranch == $branch['id']) echo 'selected'; ?>><?php echo $branch['branch_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <input type="submit" name="select_branch" value="Select Branch" class="btn btn-primary">
                </form>

                <form method="post" class="form-inline">
                    <div class="form-group">
                        <label for="club_id">Select Club:</label>
                        <select name="club_id" id="club_id" class="form-control">
                            <option value="">Select Club</option>
                            <?php if ($clubsResult) { 
                                while ($club = $clubsResult->fetch_assoc()) { ?>
                                    <option value="<?php echo $club['id']; ?>" <?php if ($selectedClub == $club['id']) echo 'selected'; ?>><?php echo $club['club_name']; ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <input type="submit" name="select_club" value="Select Club" class="btn btn-primary">
                </form>

                <?php if ($updateType == 'events') { ?>
                    <h3>Events</h3>
                    <form method="post" class="form-group">
                        <label for="event_title">Event Title:</label>
                        <input type="text" name="event_title" id="event_title" class="form-control"><br>
                        <label for="event_description">Event Description:</label>
                        <textarea name="event_description" id="event_description" class="form-control"></textarea><br>
                        <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
                        <input type="submit" name="add_event" value="Add Event" class="btn btn-success">
                    </form>

                    <h3>Existing Events</h3>
                    <ul class="list-group">
                        <?php while ($event = $eventsResult->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $event['title']; ?> (<?php echo $event['description']; ?>)
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <input type="submit" name="delete_event" value="Delete" class="btn btn-danger">
                                </form>
                            </li>
                        <?php } ?>
                    </ul>

                <?php } elseif ($updateType == 'recruitments') { ?>
                    <h3>Recruitments</h3>
                    <form method="post" class="form-group">
                        <label for="role">Role:</label>
                        <input type="text" name="role" id="role" class="form-control"><br>
                        <label for="recruitment_description">Description:</label>
                        <textarea name="recruitment_description" id="recruitment_description" class="form-control"></textarea><br>
                        <label for="deadline">Deadline:</label>
                        <input type="date" name="deadline" id="deadline" class="form-control"><br>
                        <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
                        <input type="submit" name="add_recruitment" value="Add Recruitment" class="btn btn-success">
                    </form>

                    <h3>Existing Recruitments</h3>
                    <ul class="list-group">
                        <?php while ($recruitment = $recruitmentsResult->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $recruitment['role']; ?> (<?php echo $recruitment['description']; ?>, Deadline: <?php echo $recruitment['deadline']; ?>)
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="recruitment_id" value="<?php echo $recruitment['id']; ?>">
                                    <input type="submit" name="delete_recruitment" value="Delete" class="btn btn-danger">
                                </form>
                            </li>
                        <?php } ?>
                    </ul>

                <?php } elseif ($updateType == 'applications') { ?>
                    <h3>Applications</h3>
                    <ul class="list-group">
                        <?php while ($application = $applicationsResult->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="application-details">
                                    <strong><?php echo htmlspecialchars($application['student_name']); ?></strong>
                                    <span>(Email: <?php echo htmlspecialchars($application['email']); ?>)</span>
                                </div>
                                <a href="<?php echo htmlspecialchars($application['resume_path']); ?>" target="_blank" class="btn btn-info">View Resume</a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

                <div class="alert alert-info">
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="footer">
        <div class="container">
            <div class="copyright">
                &copy; Copyright <strong><span>ClubHub</span></strong>. All Rights Reserved
            </div>
        </div>
    </footer>
</body>
</html>
