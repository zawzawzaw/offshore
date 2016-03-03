<div class="container">
  <div class="row">
    <div class="col-md-12">     
    
      @if(Auth::check())
        <div class="pull-left">
          <a href="{{ URL::to('/admin') }}" class="logo middle"><img class="mkdf-normal-logo" src="http://clients.manic.com.sg/offshore/wp-content/uploads/2016/01/company-logo-03.png" alt="logo"></a>        
        </div>

        <div class="pull-right">
          <ul>
            
              <li><a href="javascript:void(0);" class="logout-btn">Log out</a></li>
            
          </ul>        
        </div>
      @else
        <div class="pull-left">
          <a href="{{ URL::to('/admin') }}" class="logo middle"><img class="mkdf-normal-logo" src="http://clients.manic.com.sg/offshore/wp-content/uploads/2016/01/company-logo-03.png" alt="logo"></a>
        </div>        
      @endif

    </div>
  </div>
</div>
<script>
  $(document).ready(function(){
    $('.logout-btn').on('click', function(e){
      e.preventDefault();
      $(this).parent().parent('form').submit();
    });    
  });
</script>