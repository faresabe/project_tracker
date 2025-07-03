// Handle sign up
if (document.querySelector('#sign-up-form')) {
    document.querySelector('#sign-up-form').addEventListener('submit', e => {
      e.preventDefault();
  
      const firstName = document.getElementById('first-name').value;
      const lastName = document.getElementById('last-name').value;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
  
      fetch('auth_php/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ first_name: firstName, last_name: lastName, email, password })
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert('Registered! Please sign in.');
            window.location.href = 'sign_in.html';
          } else {
            alert(data.error || 'Registration failed.');
          }
        })
        .catch(err => {
          console.error(err);
          alert('Error occurred.');
        });
    });
  }
  
  // Handles sign in
  if (document.querySelector('#sign-in-form')) {
    document.querySelector('#sign-in-form').addEventListener('submit', e => {
      e.preventDefault();
  
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
  
      fetch('auth_php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert('Signed in!');
            window.location.href = '/pro_management_frontend/dashboard/board.html';
          } else {
            alert(data.error || 'Login failed.');
          }
        })
        .catch(err => {
          console.error(err);
          alert('Error occurred.');
        });
    });
  }
  