const btns = document.getElementsByClassName('voting-button');
const vote = document.getElementById('vote');
const modal = document.getElementById('voting-modal');
const modalHeader = document.getElementById('modal-header');
const names = ["Krzysztof", "Mateusz", "Michał", "Przemysław", "Przemek", "Szymon"];
const photos = ["https://zlotywas.pl/wp-content/uploads/2023/10/Krzysztof-Kostanowko-3.jpg",
                "https://zlotywas.pl/wp-content/uploads/2023/10/Mateusz-Owczarz-3.jpg",
                "https://zlotywas.pl/wp-content/uploads/2023/10/Michal-Ocheduszko-3.jpg",
                "https://zlotywas.pl/wp-content/uploads/2023/10/Przemek-Markowicz-3.jpg",
                "https://zlotywas.pl/wp-content/uploads/2023/10/Przemek-Nelec-3.jpg",
                "https://zlotywas.pl/wp-content/uploads/2023/10/Szymon-Drelich-3.jpg"];
// Get the <span> element that closes the modal
const span = document.getElementsByClassName("close")[0];
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
};

//Form fields
const getCodeForm  = document.getElementById('get-code');
const codeMsg = document.getElementById('code-msg');
const sendVoteForm = document.getElementById('send-vote');
const voteMsg = document.getElementById('vote-msg');
const photo = document.getElementById('photo');

for (btn of btns) {
  (function(btn) {
    btn.addEventListener('click', function() {
      vote.setAttribute("value", `${btn.id}`);
      photo.src = photos[btn.id-1];
      modalHeader.textContent = `${names[btn.id-1]}`;
      modal.style.display = "block";
    });
  })(btn);
}

//Handle send-code action
getCodeForm.addEventListener('submit', (event) => {
    event.preventDefault();
    const email = getCodeForm.elements['email'].value;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxurl, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onload = function() {
        if (xhr.status === 200) {
          response = JSON.parse(xhr.response);
          if(response.success) {
          getCodeForm.style.display="none";
          codeMsg.setAttribute("style", "color:green;");
          codeMsg.textContent = response.msg;
          } else {
            codeMsg.setAttribute("style", "color:red;");
            codeMsg.textContent = response.msg;
          }
        } else {
            codeMsg.setAttribute("style", "color:red;");
            codeMsg.textContent = 'Coś poszło nie tak. Spróbuj ponownie później';
        }
        getCodeForm.reset();
    };
    xhr.send(`action=send_code&email=${email}`);
});

//Handle vote action
sendVoteForm.addEventListener('submit', (event) => {
    event.preventDefault();
    const code = sendVoteForm.elements['code'].value;
    const vote = sendVoteForm.elements['vote'].value;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxurl, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onload = function() {
        if (xhr.status === 200) {
          response = JSON.parse(xhr.response);
          if(response.success) {
          getCodeForm.style.display="none";
          codeMsg.style.display="none";
          sendVoteForm.style.display="none";
          voteMsg.setAttribute("style", "color:green; text-align:center");
          voteMsg.textContent = response.msg;
          } else {
            voteMsg.setAttribute("style", "color:red;");
            voteMsg.textContent = response.msg;
          }
        } else {
            voteMsg.setAttribute("style", "color:red;");
            voteMsg.textContent = 'Coś poszło nie tak. Spróbuj ponownie później';
        }
        sendVoteForm.reset();
    };
    xhr.send(`action=vote&code=${code}&vote=${vote}`);
});