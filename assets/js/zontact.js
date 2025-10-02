(function(){
	function qs(s, r){return (r||document).querySelector(s)}
	function qsa(s, r){return (r||document).querySelectorAll(s)}
	function openModal(root, modal){
		modal.setAttribute('aria-hidden','false');
		qs('.zontact-button', root).setAttribute('aria-expanded','true');
		const first = qs('input,textarea,button', modal); if(first) first.focus();
		document.body.style.overflow='hidden';
	}
	function closeModal(root, modal){
		modal.setAttribute('aria-hidden','true');
		qs('.zontact-button', root).setAttribute('aria-expanded','false');
		document.body.style.overflow='';
	}
	function serializeForm(form){
		const data = new FormData(form);
		data.append('action','zontact_submit');
		data.append('nonce', (window.Zontact && Zontact.nonce) || '');
		return data;
	}
	function setStatus(el, msg){ el.textContent = msg || ''; }

	document.addEventListener('DOMContentLoaded', function(){
		const root = qs('.zontact-root'); if(!root) return;
		const modal = qs('#zontact-modal', root);
		const openBtn = qs('.zontact-button', root);
		const closeBtns = qsa('[data-zontact-close]', root);
		const form = qs('.zontact-form', root);
		const statusEl = qs('.zontact-status', root);

		openBtn.addEventListener('click', function(){ openModal(root, modal); });
		closeBtns.forEach(function(btn){ btn.addEventListener('click', function(){ closeModal(root, modal); }); });
		document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeModal(root, modal); } });

		// Prevent body scroll when modal is open on iOS/Android
		modal.addEventListener('wheel', function(e){}, {passive:true});

		form.addEventListener('submit', function(e){
			e.preventDefault();
			setStatus(statusEl, (Zontact && Zontact.strings && Zontact.strings.sending) || 'Sending…');
			const data = serializeForm(form);
			fetch((Zontact && Zontact.ajax_url) || '/wp-admin/admin-ajax.php', { method:'POST', body:data, credentials:'same-origin' })
				.then(function(res){ return res.json(); })
				.then(function(json){
					if(json && json.success){
						setStatus(statusEl, (Zontact && Zontact.strings && Zontact.strings.success) || 'Sent.');
						form.reset();
						setTimeout(function(){ closeModal(root, modal); setStatus(statusEl,''); }, 900);
					}else{
						setStatus(statusEl, (Zontact && Zontact.strings && Zontact.strings.error) || 'There was an error.');
					}
				})
				.catch(function(){ setStatus(statusEl, (Zontact && Zontact.strings && Zontact.strings.error) || 'There was an error.'); });
		});
	});
})();


