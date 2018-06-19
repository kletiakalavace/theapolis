$('.audio-box:not(".slick-cloned") .audio').each(function(index){
    index++;
    var waveForm = $(this).find('#waveform_'+index).selector;
    console.log(waveForm);
    var playButton = $(this).find(".playButton");
    var audioSrc = playButton.attr('data-playable');
    var duration = 0;
    var audioTime = $(this).find('.audioTime');
    var minutes = 0;
    var seconds = 0;
    var currentTime = 0;
    var currentMinutes = 0;
    var currentSeconds = 0;
    var timeRemaining = 0;

    // Load wave form
    var waveSurfer = WaveSurfer.create({
        container: waveForm,
        waveColor: "#d8d8d8",
        progressColor: "#cd2036",
        scrollParent: false,
        interact: true,
        barWidth: 4,
        fillParent: true,
        cursorWidth: 0
    });

    // load audio
    waveSurfer.load(audioSrc);

    // play audio
    playButton.click(function(){
        // waveSurfer.playPause();
        if(waveSurfer.isPlaying()){
            waveSurfer.pause();
            $(this).html('<span class="fa fa-play"></span>');
        }else{
            waveSurfer.play();
            $(this).html('<span class="fa fa-pause"></span>');
        }
    });

    // display audio duration
    waveSurfer.on('ready', function(){
        duration = waveSurfer.getDuration();
        minutes = Math.floor(duration/60) < 10 ? "0"+Math.floor(duration/60) : Math.floor(duration/60);
        seconds = Math.floor(duration - minutes * 60) < 10 ? "0"+Math.floor(duration - minutes * 60) : Math.floor(duration - minutes * 60);
        audioTime.html(minutes+":"+seconds);
    });

    // display current time
    waveSurfer.on('audioprocess', function(){
        currentTime = waveSurfer.getCurrentTime();
        timeRemaining = parseInt(duration) - parseInt(currentTime);
        currentMinutes = (Math.floor(timeRemaining/60)) < 10 ? "0"+Math.floor(timeRemaining/60) : Math.floor(timeRemaining/60);
        currentSeconds = (Math.floor(timeRemaining - currentMinutes * 60) < 10) ? "0"+Math.floor(timeRemaining - currentMinutes * 60) : Math.floor(timeRemaining - currentMinutes * 60);
        audioTime.html("-"+currentMinutes+":"+currentSeconds);
    });
});