(function(){
  const q=(s,r=document)=>r.querySelector(s), qa=(s,r=document)=>[...r.querySelectorAll(s)];
  const state={step:1,trade:'',jobs:0,source:'',used:0,demoStep:0};

  // Build the geo-grid proof visuals.
  qa('.geo-grid').forEach(grid=>{
    const fill=Math.max(0,Math.min(100,Number(grid.dataset.fill)||0));
    const green=Math.round(49*(fill/100));
    for(let i=0;i<49;i++){
      const dot=document.createElement('i');
      if(grid.classList.contains('after') && i<green) dot.classList.add('green');
      grid.appendChild(dot);
    }
  });

  // Reveal on scroll.
  const revealEls=qa('.reveal');
  if('IntersectionObserver' in window){
    const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target)}}),{threshold:.08});
    revealEls.forEach(el=>io.observe(el));
  } else revealEls.forEach(el=>el.classList.add('visible'));

  const stepper=qa('.stepper span'), questions=qa('.question'), next=q('#nextBtn'), back=q('#backBtn'), msg=q('#formMsg');
  function selectionForStep(){return [state.trade,state.jobs,state.source,state.used][state.step-1]}
  function updateAssessment(){
    questions.forEach(el=>el.classList.toggle('q-active',Number(el.dataset.step)===state.step));
    stepper.forEach((el,i)=>el.classList.toggle('active',i<state.step));
    back.disabled=state.step===1;
    next.disabled=!selectionForStep();
    next.innerHTML=state.step===4?'Show My Proof Potential <span>→</span>':'Next <span>→</span>';
    msg.textContent='';
  }
  qa('.choice').forEach(btn=>btn.addEventListener('click',()=>{
    const name=btn.dataset.name, value=btn.dataset.value;
    qa(`.choice[data-name="${name}"]`).forEach(b=>b.classList.remove('selected'));
    btn.classList.add('selected');
    state[name]=(name==='jobs'||name==='used')?Number(value):value;
    updateAssessment();
  }));
  back.addEventListener('click',()=>{if(state.step>1){state.step--;updateAssessment();}});
  next.addEventListener('click',()=>{
    if(!selectionForStep()){msg.textContent='Choose one option to continue.';return;}
    if(state.step<4){state.step++;updateAssessment();return;}
    showResult();
  });
  function showResult(){
    const annual=Math.max(0,state.jobs*52); const usedAnnual=Math.min(annual,state.used*52); const unused=Math.max(0,annual-usedAnnual);
    q('#annualJobs').textContent=annual.toLocaleString(); q('#unusedJobs').textContent=unused.toLocaleString();
    q('#demoTrade').textContent=(state.trade||'Home-service')+' job';
    const pct=annual?Math.round((unused/annual)*100):0;
    q('#resultSentence').textContent=pct>=80?`About ${pct}% of your completed jobs may be disappearing instead of becoming public proof.`:`That leaves roughly ${unused.toLocaleString()} real jobs per year that could be doing more after the invoice is paid.`;
    q('#resultCard').classList.add('show'); q('#resultCard').scrollIntoView({behavior:'smooth',block:'center'});
    q('#sourceText').textContent=state.source?`${state.source} → JobCapturePro`:'JCP App or connected workflow';
    renderDemo();
  }
  updateAssessment();

  const demoSteps=[
    {title:'A real job gets completed.',body:'Your technician finishes the job and captures a photo — or the completed job flows in from the system your team already uses.',detail:()=>state.source?`${state.source} → JobCapturePro`:'JCP App or connected workflow',type:'job'},
    {title:'JobCapturePro creates the check-in.',body:'Job photos, service context and location become a structured, channel-ready check-in. AI helps turn the raw job data into usable public proof.',detail:()=>`Real ${state.trade||'contractor'} work → structured proof`,type:'jcp'},
    {title:'The job becomes website proof.',body:'Instead of dying in a camera roll, the job can become a real page or proof block on your website — with the work, service and location visible to homeowners.',detail:()=>`Website proof for ${state.trade||'your trade'}`,type:'web'},
    {title:'Your Google profile stays active.',body:'A completed job can become fresh Google Business Profile activity built around work you actually performed — not another generic promotion.',detail:()=>`Fresh local job activity`,type:'google'},
    {title:'The review opportunity happens while the job is fresh.',body:'Use a QR code or link to make the review ask easy while the customer still remembers the experience and your work is right in front of them.',detail:()=>`Completed job → review opportunity`,type:'review'},
    {title:'One job becomes multiple marketing assets.',body:'The same completed job can support your website, Google, social presence, directory listing and customer-review workflow. You do the work once. JobCapturePro makes it visible.',detail:()=>`${(state.jobs||20)*52} jobs/year = a lot of proof potential`,type:'fan'}
  ];
  const progress=q('#demoProgress');
  for(let i=0;i<demoSteps.length;i++){const s=document.createElement('span');progress.appendChild(s)}
  function renderDemo(){
    const d=demoSteps[state.demoStep];
    q('#demoStepLabel').textContent=`STEP ${state.demoStep+1} OF ${demoSteps.length}`; q('#demoTitle').textContent=d.title; q('#demoBody').textContent=d.body; q('#sourceText').textContent=d.detail();
    qa('#demoProgress span').forEach((el,i)=>el.classList.toggle('done',i<=state.demoStep));
    q('#demoPrev').disabled=state.demoStep===0; q('#demoNext').innerHTML=state.demoStep===demoSteps.length-1?'Start My 14-Day Trial <span>→</span>':'Next <span>→</span>';
    const c=q('#demoCanvas'), trade=state.trade||'HVAC', location=trade==='HVAC'?'Austin, TX':'Your service area';
    if(d.type==='job') c.innerHTML=`<div class="canvas-job"><div class="canvas-head"><span>COMPLETED JOB</span><span>Today · 2:14 PM</span></div><div class="canvas-body"><div class="canvas-photo hvac-art"></div><div class="canvas-meta"><span>${trade} service</span><span>${location}</span><span>Real job photos</span><span>Job complete ✓</span></div></div></div>`;
    if(d.type==='jcp') c.innerHTML=`<div class="canvas-jcp"><div class="canvas-head"><span>◆ JOBCAPTUREPRO</span><span>Creating check-in…</span></div><div class="canvas-body"><div class="canvas-photo hvac-art" style="height:150px"></div><div class="scan-list"><span>Scanning job photos</span><span>Identifying service context</span><span>Pinning the job area</span><span>Writing channel-ready copy</span><span>Preparing publishable proof</span></div></div></div>`;
    if(d.type==='web') c.innerHTML=`<div class="canvas-web"><div class="canvas-head"><span>YOUR WEBSITE</span><span>Recent work</span></div><div class="canvas-body"><div class="website-shell"><div class="website-nav">YOUR ${trade.toUpperCase()} COMPANY · SERVICES · AREAS · REVIEWS</div><div class="website-content"><div class="canvas-photo hvac-art" style="height:150px"></div><h4>Recent ${trade} Job in ${location}</h4><p>Real completed work, real photos and service context — published as proof a homeowner can actually see.</p></div></div></div></div>`;
    if(d.type==='google') c.innerHTML=`<div class="canvas-google"><div class="canvas-head"><span>GOOGLE BUSINESS PROFILE</span><span>Fresh update</span></div><div class="canvas-body"><div class="google-shell"><div class="google-brand"><span style="color:#4285f4">G</span> Your ${trade} Company</div><div class="canvas-photo hvac-art" style="height:160px"></div><p class="google-copy"><b>Recent ${trade} work in ${location}.</b><br>Another completed job, documented with real photos and useful service context.</p></div></div></div>`;
    if(d.type==='review') c.innerHTML=`<div class="canvas-review"><div class="canvas-head"><span>REVIEW OPPORTUNITY</span><span>Sent after completion</span></div><div class="canvas-body"><div class="review-msg">Thanks for choosing us today. If you have a minute, would you mind sharing your experience? Your feedback helps future customers know what to expect.</div><div class="review-button">Leave a Review ★★★★★</div><p style="color:#6b7280;font-size:12px;margin-top:14px">QR code or direct link · customer chooses whether to review</p></div></div>`;
    if(d.type==='fan') c.innerHTML=`<div class="canvas-fan"><div class="canvas-head"><span>ONE COMPLETED JOB</span><span>→ MULTIPLE OPPORTUNITIES</span></div><div class="canvas-body"><div class="fan-grid"><div><b>Website proof</b><span>Real job content</span></div><div><b>Google activity</b><span>Fresh GBP update</span></div><div><b>Review opportunity</b><span>QR or link</span></div><div><b>Social content</b><span>Ready-to-use proof</span></div><div><b>Directory presence</b><span>Verified activity</span></div><div><b>Local visibility</b><span>Proof across your service area</span></div></div></div></div>`;
  }
  q('#demoPrev').addEventListener('click',()=>{if(state.demoStep>0){state.demoStep--;renderDemo()}});
  q('#demoNext').addEventListener('click',()=>{if(state.demoStep<demoSteps.length-1){state.demoStep++;renderDemo()}else{window.open('https://app.jobcapturepro.com/','_blank','noopener')}});
  renderDemo();

  // Preserve attribution params on trial links.
  const params=new URLSearchParams(location.search); const wanted=['utm_source','utm_medium','utm_campaign','utm_content','utm_term'];
  qa('.trial-link').forEach(a=>{const u=new URL(a.href);wanted.forEach(k=>{if(params.get(k))u.searchParams.set(k,params.get(k))});a.href=u.toString()});
})();
