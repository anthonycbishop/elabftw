/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
import { Api } from './Apiv2.class';
import Todolist from './Todolist.class';
import { Malle } from '@deltablot/malle';
import i18next from 'i18next';
import { Model } from './interfaces';

document.addEventListener('DOMContentLoaded', () => {
  if (!document.getElementById('info')) {
    return;
  }

  const pagesWithoutTodo = ['login', 'register', 'change-pass'];
  if (pagesWithoutTodo.includes(document.getElementById('info').dataset.page)) {
    return;
  }

  let unfinishedStepsScope = 'user';
  // unfinished steps scopeSwitch i.e. user (0) or team (1)
  let scopeSwitch = document.getElementById(Model.Todolist + 'StepsShowTeam') as HTMLInputElement;
  // anon user
  if (scopeSwitch === null) {
    return;
  }
  const storageScopeSwitch = localStorage.getItem(Model.Todolist + 'StepsShowTeam');
  // adjust scope from localStorage
  if (scopeSwitch.checked && storageScopeSwitch === '0') {
    scopeSwitch.checked = false;

  // set storage value if default setting is team
  } else if (scopeSwitch.checked) {
    localStorage.setItem(Model.Todolist + 'StepsShowTeam', '1');
    unfinishedStepsScope = 'team';

  // check box if it was checked before
  } else if (storageScopeSwitch === '1') {
    scopeSwitch.checked = true;
    unfinishedStepsScope = 'team';
  }

  const TodolistC = new Todolist();
  const ApiC = new Api();
  TodolistC.unfinishedStepsScope = unfinishedStepsScope;

  // TOGGLE
  // reopen to-do list panel if it was previously opened
  if (localStorage.getItem(`is${TodolistC.model}Open`) === '1') {
    TodolistC.toggle();
  }
  scopeSwitch = document.getElementById(TodolistC.model + 'StepsShowTeam') as HTMLInputElement;
  scopeSwitch.addEventListener('change', () => {
    if (!document.getElementById(TodolistC.panelId).hasAttribute('hidden')){
      TodolistC.toggleUnfinishedStepsScope();
    }
  });

  // to avoid duplicating code between listeners (keydown and click on add)
  function createTodoitem(): void {
    const todoInput = (document.getElementById('todo') as HTMLInputElement);
    const content = todoInput.value;
    if (!content) { return; }

    TodolistC.create(content).then(() => {
      // reload the todolist
      TodolistC.display();
      // and clear the input
      todoInput.value = '';
    });
  }

  // UPDATE TODOITEM
  const malleableTodoitem = new Malle({
    // will only work if the editable class is present (class is removed on check)
    before: original => {
      return original.classList.contains('editable');
    },
    inputClasses: ['form-control'],
    fun: async (value, original) => {
      return ApiC.patch(`${Model.Todolist}/${original.dataset.todoitemid}`, {'content': value})
        .then(resp => resp.json()).then(json => json.body);
    },
    listenOn: '.todoItem',
    tooltip: i18next.t('click-to-edit'),
  }).listen();

  // add an observer so new todo items will get an event handler too
  new MutationObserver(() => {
    malleableTodoitem.listen();
  }).observe(document.getElementById('todoItems'), {childList: true});

  // save todo on enter
  document.getElementById('todo').addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
      createTodoitem();
    }
  });

  // Add click listener and do action based on which element is clicked
  document.getElementById('container').addEventListener('click', event => {
    const el = (event.target as HTMLElement);
    // CREATE TODOITEM
    if (el.matches('[data-action="create-todoitem"]')) {
      createTodoitem();

    // DESTROY TODOITEM
    } else if (el.matches('[data-action="destroy-todoitem"]')) {
      const todoitemId = parseInt(el.dataset.todoitemid);
      TodolistC.destroy(todoitemId).then(() => {
        // check item text
        const content = (el.nextElementSibling as HTMLSpanElement);
        content.style.textDecoration = 'line-through';
        // make it non editable (before function checks for that in malle)
        content.classList.remove('editable');
        // disable the checkbox
        el.setAttribute('disabled', 'disabled');
      });

    // TOGGLE TODOLIST
    } else if (el.matches('[data-action="toggle-todolist"]')) {
      TodolistC.toggle();
    }
  });
});
