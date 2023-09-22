// React & Vendor Libs
const { render, useEffect, useRef, useState, useMemo } = wp.element;

// AI Engine
import { mwaiHandleRes, mwaiFetch, OutputHandler } from '@app/helpers';

const errorsContainer = {
  background: '#711f1f',
  color: '#fff',
  padding: '15px 30px',
  borderRadius: '10px',
  marginTop: '10px'
};

const FormSubmit = (props) => {
  // eslint-disable-next-line no-unused-vars
  const { system, params, theme } = props;
  const [ isLoading, setIsLoading ] = useState(false);
  const [ isValid,  setIsValid ] = useState(false);
  const [ fields, setFields ] = useState({});
  const refSubmit = useRef(null);
  const refContainer = useRef(null);
  const [ serverReply, setServerReply ] = useState();
  const [ errors, setErrors ] = useState([]);

  // System Params
  const { id,  stream = false, formId, sessionId, contextId, restNonce, restUrl, debugMode } = system;
  
  // Front Params
  const { label, outputElement, inputs } = params;

  useEffect(() => {
    const handlePageLoad = () => {
      const container = refSubmit.current.closest('.mwai-form-container');
      if (!inputs) {
        //alert("The 'Inputs' are not defined.");
        setErrors(errors => [...errors, "The 'Inputs' are not defined."]);
        return;
      }
      refContainer.current = container;
      const inputElements = [];
      inputs.selectors.forEach(selector => {
        const element = document.querySelector(selector);
        if (!element) {
          //alert(`The 'Input Field' (selector) was not found (${selector}).`);
          setErrors(errors => [...errors, `The 'Input Field' (selector) was not found (${selector}).`]);
          return;
        }
        inputElements.push({ selector, element });
      });
      inputs.fields.forEach(field => {
        const element = refContainer.current.querySelector(`fieldset[data-form-name='${field}']`);
        if (!element) {
          //alert(`The 'Input Field' (element) was not found (${field}).`);
          setErrors(errors => [...errors, `The 'Input Field' (element) was not found (${field}).`]);
          return;
        }
        const subType = element.getAttribute('data-form-type');
        const required = element.getAttribute('data-form-required') === 'true';
        inputElements.push({ field, subType, element, required });
      });

      inputElements.forEach(inputElement => {
        onInputElementChanged(inputElement);
        inputElement.element.addEventListener('change', () => { onInputElementChanged(inputElement); });
        inputElement.element.addEventListener('keyup', () => { onInputElementChanged(inputElement); });
        if (inputElement.selector) {
          const observer = new MutationObserver(() => { onInputElementChanged(inputElement); });
          observer.observe(inputElement.element, { childList: true, subtree: true });
        }
        else {
          // console.warn("The 'Input Field' (element) is not a selector, so it will not be observed for changes.", { inputElement });
        }
      });
    };

    // Check if the document has already loaded, if so, run the function directly.
    if (document.readyState === 'complete') {
      handlePageLoad();
    } else {
      // Otherwise, wait for the page to load.
      window.addEventListener('load', handlePageLoad);
    }

    // Cleanup
    return () => {
      window.removeEventListener('load', handlePageLoad);
    };
  }, [inputs]);

  useEffect(() => {
    const output = document.querySelector(outputElement);
    if (!serverReply) { return; }
    if (!output) { 
      //alert(`The 'Output' was not found (${outputElement ?? 'N/A'}).`);
      if (!errors.includes(`The 'Output' was not found (${outputElement ?? 'N/A'}).`)) {
        setErrors(errors => [...errors, `The 'Output' was not found (${outputElement ?? 'N/A'}).`]);
      }
      return;
    }
    const { success, reply, message } = serverReply;
    if (success) {
      render(<OutputHandler baseClass="mwai-form-output" content={reply}
        isStreaming={isLoading && stream} />, output);
    }
    else {
      render(<OutputHandler baseClass="mwai-form-output" error={message}
        isStreaming={isLoading && stream} />, output);
    }
  }, [isLoading, stream, serverReply]);

  // Update the content of the fields.
  const onInputElementChanged = async (inputElement) => {
    const key = inputElement.field ?? inputElement.selector;
    const currentVal = fields[key] ?? null;
    let newVal = null;
    let isValid = true;

    if (!inputElement.field) {
      // It's a Selector Input, let's check for checkboxes.
      const checkboxes = [...inputElement.element.querySelectorAll('input[type="checkbox"]')];
      if (checkboxes.length > 0) {
        inputElement.subType = 'checkbox';
      }
      // It's a Selector Input, let's check for radios.
      const radios = [...inputElement.element.querySelectorAll('input[type="radio"]')];
      if (radios.length > 0) {
        inputElement.subType = 'radio';
      }
    }

    if (inputElement.subType === 'checkbox') {
      const checkboxes = [...inputElement.element.querySelectorAll('input[type="checkbox"]')];
      newVal = checkboxes.filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
      if (debugMode) { 
        // eslint-disable-next-line no-console
        console.log(`AI Forms: Form ${id} => Checkbox Updated`, { key, field: inputElement.field, 
          subType: inputElement.subType, currentVal, newVal, hasChanges, inputElement });
      }
    }
    else if (inputElement.subType === 'radio') {
      const radios = [...inputElement.element.querySelectorAll('input[type="radio"]')];
      const radio = radios.find(radio => radio.checked);
      newVal = radio ? radio.value : null;
      isValid = !inputElement.required || (newVal && newVal !== '');
      if (debugMode) { 
        // eslint-disable-next-line no-console
        console.log(`AI Forms: Form ${id} => Radio Updated`, { key, field: inputElement.field, 
          subType: inputElement.subType, currentVal, newVal, hasChanges, inputElement });
      }
    }
    else if (inputElement.field) {
      const input = inputElement.element.querySelector(inputElement.subType);
      newVal = input.value;
      isValid = !(inputElement.required && (!newVal || newVal === ''));
      if (debugMode) { 
        // eslint-disable-next-line no-console
        console.log(`AI Forms: Form ${id} => Field Updated`, { key, field: inputElement.field, 
          subType: inputElement.subType, currentVal, newVal, hasChanges, inputElement });
      }
    }
    else if (inputElement.selector) {
      newVal = inputElement.element.textContent.trim();
      if (newVal === '') { 
        newVal = inputElement.element.value;
      }
      if (debugMode) { 
        // eslint-disable-next-line no-console
        console.log(`AI Forms: Form ${id} => Selector Updated`, { key, field: inputElement.field, 
          subType: inputElement.subType, currentVal, newVal, hasChanges, inputElement });
      }
    }
    else {
      console.error("AI Forms: Cannot recognize the changes on this inputElement.", { key, currentVal, inputElement });
    }

    const hasChanges = currentVal !== newVal;

    if (hasChanges) {
      fields[key] = newVal;
      setFields({ ...fields });
    }
    setIsValid(isValid);
  };

  const onSubmitClick = async () => {
    setIsLoading(true);
    setServerReply({ success: true, reply: '' });

    const body = {
      id: id,
      formId: formId,
      session: sessionId,
      contextId: contextId,
      stream,
      fields
    };

    try {
      if (debugMode) { 
        // eslint-disable-next-line no-console
        console.log('[FORMS] OUT: ', body);
      }
      const streamCallback = !stream ? null : (content) => {
        setServerReply({ success: true, reply: content });
      };

      // Let's perform the request. The mwaiHandleRes will handle the complexity of response.
      const res = await mwaiFetch(`${restUrl}/mwai-ui/v1/forms/submit`, body, restNonce, stream);
      const data = await mwaiHandleRes(res, streamCallback, debugMode ? "FORMS" : null);
      setServerReply(data);
      if (debugMode) {
        // eslint-disable-next-line no-console
        console.log('[FORMS] IN: ', data);
      }
    }
    catch (err) {
      console.error("An error happened in the handling of the forms response.", { err });
    }
    finally {
      setIsLoading(false);
    }
  };

  const baseClasses = useMemo(() => {
    const classes = ['mwai-form-submit'];
    if (isLoading) {
      classes.push('mwai-loading');
    }
    return classes;
  }, [isLoading]);

  return (
    <div ref={refSubmit} className={baseClasses.join(' ')}>
      <button id={id} disabled={!isValid || isLoading} onClick={onSubmitClick}>
        <span>{label}</span>
      </button>
      {errors.length > 0 && (
        <ul className="mwai-forms-errors" style={errorsContainer}>
          {errors.map((error, index) => (
            <li key={index}>{error}</li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default FormSubmit;
