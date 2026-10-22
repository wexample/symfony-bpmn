import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_task_right.json';

export default class BpmnTaskRight extends AbstractApiEntity {
  static readonly entityName = 'bpmnTaskRight';

  static retrieveEntitySchema() {
    return schema;
  }
}
