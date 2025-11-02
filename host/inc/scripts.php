<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>
  function alert(type,msg,position='body')
  {
    let bs_class = (type == 'success') ? 'alert-success' : 'alert-danger';
    let element = document.createElement('div');
    element.innerHTML = `
      <div class="alert ${bs_class} alert-dismissible fade show" role="alert">
        <strong class="me-3">${msg}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;

    if(position=='body'){
      document.body.append(element);
      element.classList.add('custom-alert');
    }
    else{
      document.getElementById(position).appendChild(element);
    }
    setTimeout(remAlert, 2000);
  }

  function remAlert(){
    const alerts = document.getElementsByClassName('alert');
    if(alerts.length){
      alerts[0].remove();
    }
  }

  function setActive(){
    const navbar = document.getElementById('dashboard-menu');
    if(!navbar){
      return;
    }
    const links = navbar.getElementsByTagName('a');
    for(let i=0; i<links.length; i++){
      const file = links[i].href.split('/').pop();
      const fileName = file.split('.')[0];
      if(document.location.href.indexOf(fileName) >= 0){
        links[i].classList.add('active');
      }
    }
  }
  setActive();
</script>
