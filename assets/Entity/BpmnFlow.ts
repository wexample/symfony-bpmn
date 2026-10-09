import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_flow.json';

export default class BpmnFlow extends AbstractApiEntity {
  static readonly entityName = 'bpmnFlow';

  static retrieveEntitySchema() {
    return schema;
  }
}
