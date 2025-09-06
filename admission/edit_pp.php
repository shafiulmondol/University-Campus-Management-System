<?php
// admin_users.php  (single-file frontend + backend)
// ---------------- CONFIG (change if needed) ----------------
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'skst_university';

// ---------------- BACKEND ROUTER ----------------
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $con = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($con->connect_error) {
        echo json_encode(['success' => false, 'error' => $con->connect_error]);
        exit;
    }
    $action = $_GET['action'];

    // 1) return list of emails for dropdown
    if ($action === 'getEmails') {
        $res = $con->query("SELECT id, email, username FROM admin_users ORDER BY email");
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success' => true, 'emails' => $rows]);
        exit;
    }

    // 2) verify password for a given email (POST JSON { email, password })
    if ($action === 'verifyPassword') {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        $stmt = $con->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            // NOTE: your DB currently stores plain text passwords in the dump.
            // In production use password_hash() and password_verify() instead.
            if ($user['password'] === $password) {
                echo json_encode(['success' => true, 'user' => $user]);
                exit;
            }
        }
        echo json_encode(['success' => false]);
        exit;
    }

    // 3) update profile (POST JSON with fields). We'll only update changed fields.
    if ($action === 'updateProfile') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }

        // Allowed fields and their types for bind_param (s = string, i = integer)
        $allowed = [
            'full_name' => 's',
            'username' => 's',
            'password' => 's',
            'email' => 's',
            'phone' => 's',
            'key' => 'i',
            'profile_picture' => 's',
            'is_active' => 'i'
        ];

        // fetch current row
        $old = $con->query("SELECT * FROM admin_users WHERE id = " . intval($id))->fetch_assoc();
        if (!$old) { echo json_encode(['success' => false, 'error' => 'User not found']); exit; }

        $updates = [];
        $params = [];
        $types = '';
        $changes = [];

        foreach ($allowed as $field => $typ) {
            if (array_key_exists($field, $data)) {
                $newVal = $data[$field];
                if ($field === 'is_active') $newVal = intval($newVal) ? 1 : 0;
                // compare as string to detect changes consistently
                $oldVal = array_key_exists($field, $old) ? $old[$field] : null;
                if ((string)$oldVal !== (string)$newVal) {
                    $updates[] = "`$field` = ?";
                    $params[] = $newVal;
                    $types .= $typ;
                    $changes[] = ['field' => $field, 'old' => $oldVal, 'new' => $newVal];
                }
            }
        }

        if (count($updates) === 0) {
            echo json_encode(['success' => true, 'changes' => []]);
            exit;
        }

        // Build prepared statement
        // NOTE: admin_users table does not have updated_at in your dump, so we won't set it.
        $sql = "UPDATE admin_users SET " . implode(', ', $updates) . " WHERE id = ?";
        $types .= 'i';
        $params[] = $id;

        $stmt = $con->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => $con->error]);
            exit;
        }

        // dynamic bind_param (need references)
        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) $bind_names[] = &$params[$i];
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
            exit;
        }

        echo json_encode(['success' => true, 'changes' => $changes]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin User Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  body { background:#f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  .card { border-radius:15px; box-shadow:0 0.5rem 1rem rgba(0,0,0,0.12); margin-bottom:20px; }
  .card-header { background:linear-gradient(45deg,#4e73df,#224abe); color:#fff; border-radius:15px 15px 0 0 !important; }
  .btn-primary { background:linear-gradient(45deg,#4e73df,#224abe); border:none; }
  .btn-primary:hover { background:linear-gradient(45deg,#224abe,#4e73df); }
  .form-section { padding:18px; border-left:4px solid #4e73df; background:#fff; border-radius:6px; margin-bottom:16px; }
  .username-display { font-weight:700; color:#4e73df; font-size:1.1rem; }
  .profile-preview { max-width:120px; max-height:120px; border-radius:8px; border:1px solid #ddd; object-fit:cover; }
</style>
</head>
<body>
<div class="container py-5">
  <div class="text-center mb-4">
    <h1 class="mb-0"><i class="fas fa-users-cog me-2"></i>Admin User Management</h1>
    <p class="text-muted">Select an admin → verify password → edit all columns → confirm</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
           
    <a href="admission_officer.php" class="btn btn-primary">
      <i class="fas fa-arrow-left me-2"></i>Back
    </a>
 
          <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Select Admin Email</h5>
        </div>
        
        <div class="card-body">

          <!-- Step 1: email selection -->
          <div class="form-section" id="stepEmail">
            <div class="mb-3">
              <label class="form-label">Select Email</label>
              <select id="emailSelect" class="form-select"><option value="">-- loading emails --</option></select>
            </div>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <div class="username-display" id="usernameDisplay">Not selected</div>
            </div>
            <div>
              <button id="btnProceed" class="btn btn-primary">Proceed to Password Verification <i class="fas fa-arrow-right ms-2"></i></button>
            </div>
          </div>

          <!-- Step 2: password -->
          <div class="form-section" id="stepPassword" style="display:none">
            <form id="passwordForm" onsubmit="return false;">
              <div class="mb-3">
                <label class="form-label">Enter Password</label>
                <input type="password" id="passwordInput" class="form-control" placeholder="Enter current password">
              </div>
              <div class="alert alert-danger" id="passwordError" style="display:none">Incorrect password — try again</div>
              <div><button id="btnVerify" class="btn btn-primary">Verify Password <i class="fas fa-check ms-2"></i></button></div>
            </form>
          </div>

          <!-- Step 3: edit profile (contains all columns) -->
          <div class="form-section" id="stepEdit" style="display:none">
            <h6 class="mb-3"><i class="fas fa-user-edit me-2"></i>Edit Profile (all editable columns)</h6>
            <form id="editForm" onsubmit="return false;">
              <input type="hidden" id="userId" />

              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Username</label>
                  <input id="username" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input id="email" type="email" class="form-control" />
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <input id="password" class="form-control" />
                  <div class="form-text">Current passwords stored plain-text in DB dump — consider hashing.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input id="fullName" class="form-control" />
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input id="phone" class="form-control" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Profile Picture URL</label>
                  <input id="profilePicture" type="url" class="form-control" placeholder="https://...">
                </div>
              </div>

              <div class="d-flex align-items-center gap-3 mb-3">
                <div>
                  <img id="profilePreview" class="profile-preview" src="" alt="Preview" style="display:none">
                </div>
                <div>
                  <small class="text-muted">Paste an image URL above to preview and save.</small>
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-4">
                  <label class="form-label">Admin Key (`key`)</label>
                  <input id="keyField" type="number" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Role</label>
                  <input id="role" type="text" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Is Active</label>
                  <select id="isActive" class="form-select">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
              </div>

              <div class="mt-3">
                <button id="btnUpdate" class="btn btn-primary">Update Profile <i class="fas fa-save ms-2"></i></button>
              </div>
            </form>
          </div>

          <!-- Step 4: confirmation -->
          <div class="form-section" id="stepConfirm" style="display:none">
            <h5><i class="fas fa-check-circle text-success me-2"></i>Profile Updated</h5>
            <div id="confirmChanges" class="mb-3"></div>
            <div>
              <button id="btnBackEdit" class="btn btn-secondary me-2">Back to Edit</button>
              <button id="btnBackStart" class="btn btn-primary">Back to Start</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- scripts -->
<script>
const apiBase = '?action=';

// helper update step indicator (simple)
function updateStepVisual(step) {
  // no step indicator visual in this simplified UI, but could be added
  // show/hide blocks handled in code below
}

// load emails
function loadEmails() {
  fetch(apiBase + 'getEmails').then(r => r.json()).then(data => {
    const sel = document.getElementById('emailSelect');
    sel.innerHTML = '<option value="">-- select email --</option>';
    if (data.success) {
      data.emails.forEach(e => {
        const opt = document.createElement('option');
        opt.value = e.email;
        opt.textContent = e.email;
        opt.dataset.username = e.username;
        opt.dataset.id = e.id;
        sel.appendChild(opt);
      });
    } else {
      sel.innerHTML = '<option value="">-- failed to load --</option>';
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  loadEmails();

  const emailSelect = document.getElementById('emailSelect');
  const usernameDisplay = document.getElementById('usernameDisplay');
  const btnProceed = document.getElementById('btnProceed');
  const stepPassword = document.getElementById('stepPassword');
  const passwordInput = document.getElementById('passwordInput');
  const passwordError = document.getElementById('passwordError');
  const btnVerify = document.getElementById('btnVerify');
  const stepEdit = document.getElementById('stepEdit');
  const editForm = document.getElementById('editForm');
  const btnUpdate = document.getElementById('btnUpdate');
  const stepConfirm = document.getElementById('stepConfirm');
  const confirmChanges = document.getElementById('confirmChanges');
  const btnBackEdit = document.getElementById('btnBackEdit');
  const btnBackStart = document.getElementById('btnBackStart');

  // show username for selected email
  emailSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    usernameDisplay.textContent = (opt && opt.dataset && opt.dataset.username) ? opt.dataset.username : 'Not selected';
  });

  // proceed to password
  btnProceed.addEventListener('click', function() {
    if (!emailSelect.value) { alert('Please choose an email first'); return; }
    stepPassword.style.display = 'block';
    passwordInput.focus();
  });

  // verify password
  btnVerify.addEventListener('click', function() {
    const email = emailSelect.value;
    const pass = passwordInput.value;
    passwordError.style.display = 'none';
    fetch(apiBase + 'verifyPassword', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ email, password: pass })
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        // fill edit form with user data from DB
        const u = res.user;
        document.getElementById('userId').value = u.id;
        document.getElementById('username').value = u.username || '';
        document.getElementById('email').value = u.email || '';
        document.getElementById('password').value = u.password || '';
        document.getElementById('fullName').value = u.full_name || '';
        document.getElementById('phone').value = u.phone || '';
        document.getElementById('profilePicture').value = u.profile_picture || '';
        document.getElementById('keyField').value = u.key || '';
        document.getElementById('role').value = u.role || '';
        document.getElementById('isActive').value = (u.is_active == 1 ? '1' : '0');

        // preview profile picture if any
        updatePreview(u.profile_picture || '');

        // show edit section
        stepEdit.style.display = 'block';
        // optionally hide password section to move forward
        // document.getElementById('stepPassword').style.display = 'none';
      } else {
        passwordError.style.display = 'block';
      }
    })
    .catch(err => { console.error(err); passwordError.style.display = 'block'; });
  });

  // profile picture preview logic
  const profilePictureInput = document.getElementById('profilePicture');
  const profilePreview = document.getElementById('profilePreview');
  function updatePreview(url) {
    if (!url) { profilePreview.style.display = 'none'; profilePreview.src = ''; return; }
    profilePreview.src = url;
    profilePreview.onload = () => profilePreview.style.display = 'block';
    profilePreview.onerror = () => { profilePreview.style.display = 'none'; profilePreview.src = ''; };
  }
  profilePictureInput.addEventListener('input', (e) => updatePreview(e.target.value.trim()));

  // update action
  btnUpdate.addEventListener('click', function() {
    const payload = {
      id: parseInt(document.getElementById('userId').value || 0, 10),
      username: document.getElementById('username').value,
      email: document.getElementById('email').value,
      password: document.getElementById('password').value,
      full_name: document.getElementById('fullName').value,
      phone: document.getElementById('phone').value,
      key: document.getElementById('keyField').value ? parseInt(document.getElementById('keyField').value,10) : 0,
      profile_picture: document.getElementById('profilePicture').value,
      is_active: parseInt(document.getElementById('isActive').value,10) ? 1 : 0
    };

    fetch(apiBase + 'updateProfile', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        // show confirmation with changes
        confirmChanges.innerHTML = '';
        if (res.changes && res.changes.length > 0) {
          res.changes.forEach(ch => {
            confirmChanges.innerHTML += `<div class="alert alert-success"><strong>${ch.field}</strong> changed from <span class="text-danger">${ch.old}</span> to <span class="text-success">${ch.new}</span></div>`;
          });
        } else {
          confirmChanges.innerHTML = '<div class="alert alert-info">No changes made.</div>';
        }
        // hide edit, show confirm
        stepEdit.style.display = 'none';
        stepConfirm.style.display = 'block';
      } else {
        alert('Update failed: ' + (res.error || 'unknown error'));
      }
    })
    .catch(err => { console.error(err); alert('Update failed (see console)'); });
  });

  // back buttons
  btnBackEdit.addEventListener('click', function() {
    stepConfirm.style.display = 'none';
    stepEdit.style.display = 'block';
  });
  btnBackStart.addEventListener('click', function() {
    location.reload();
  });

});
</script>
</body>
</html>
