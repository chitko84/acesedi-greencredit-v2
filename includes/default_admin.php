<?php
function gc_ensure_default_admin(mysqli $conn): void
{
    static $hasRun = false;
    if ($hasRun) {
        return;
    }
    $hasRun = true;

    $name = 'System Administrator';
    $dateOfBirth = '2000-01-01';
    $phoneNumber = '0000000000';
    $email = 'admin@greencredit.local';
    $passwordHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $role = 'admin';
    $ecoPoints = 0;
    $profilePic = 'default-profile.jpg';
    $programOfStudy = 'N/A';
    $intake = 'N/A';
    $country = 'N/A';
    $gender = 'Other';
    $department = 'Admin';
    $expectedGraduationYear = 2026;

    $sql = "INSERT INTO users (
                name,
                date_of_birth,
                phone_number,
                email,
                password,
                role,
                eco_points,
                profile_pic,
                program_of_study,
                intake,
                country,
                gender,
                department,
                expected_graduation_year
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                date_of_birth = VALUES(date_of_birth),
                phone_number = VALUES(phone_number),
                password = VALUES(password),
                role = VALUES(role),
                eco_points = VALUES(eco_points),
                profile_pic = VALUES(profile_pic),
                program_of_study = VALUES(program_of_study),
                intake = VALUES(intake),
                country = VALUES(country),
                gender = VALUES(gender),
                department = VALUES(department),
                expected_graduation_year = VALUES(expected_graduation_year)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Default admin prepare failed: ' . $conn->error);
        return;
    }

    $stmt->bind_param(
        'ssssssissssssi',
        $name,
        $dateOfBirth,
        $phoneNumber,
        $email,
        $passwordHash,
        $role,
        $ecoPoints,
        $profilePic,
        $programOfStudy,
        $intake,
        $country,
        $gender,
        $department,
        $expectedGraduationYear
    );

    if (!$stmt->execute()) {
        error_log('Default admin upsert failed: ' . $stmt->error);
    }

    $stmt->close();
}
